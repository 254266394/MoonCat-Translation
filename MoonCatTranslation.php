<?php
/**
 * 月半猫翻译
 * 作者：芸芸众生
 * 日期：2025年2月1日
 * 功能：提供中英文互译功能，支持缓存、数据库词典加载、分词及异步翻译。
 * 需要安装 Fukuball/jieba 和 React 依赖，启用 APCu 扩展。
**/

require 'vendor/autoload.php';

use Fukuball\Jieba\Jieba;
use Fukuball\Jieba\Finalseg;
use React\EventLoop\Factory;
use React\Promise\Promise;


class TranslationService {
    private $dictionaries = [];
    private $dbConnection;
    public function __construct(
        $dbHost = 'localhost',
        $dbName = 'translation_db',
        $dbUser = 'root',
        $dbPass = 'password'
    ) {
        $this->initializeJieba();
        $this->initializeDatabase($dbHost, $dbName, $dbUser, $dbPass);
    }

    /**
     * 初始化jieba分词工具
     */
    private function initializeJieba()
    {
        Jieba::init(['mode' => 'default', 'dict' => 'small']);
        Finalseg::init();
    }

    
    private function initializeDatabase($host, $dbname, $user, $pass)
    {
        try {
            $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
            $this->dbConnection = new PDO($dsn, $user, $pass);
            $this->dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("数据库连接失败：" . $e->getMessage());
        }
    }

    /**
     * 从数据库加载指定类型的词典
     * @param string $type 词典类型（'en2zh'、'zh2en'）
     */
    public function loadWordDictionary($type)
    {
        try {
            $stmt = $this->dbConnection->prepare("
                SELECT source_text, target_text 
                FROM dictionaries 
                WHERE translation_type = :type
            ");
            $stmt->execute([':type' => $type]);
            
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $this->dictionaries[$type][$row['source_text']] = $row['target_text'];
            }
        } catch (PDOException $e) {
            throw new Exception("加载词典失败：" . $e->getMessage());
        }
    }

    /**
     * 翻译文本
     * @param string $text 待翻译文本
     * @param string $target 目标语言（默认 'en2zh'，英文转中文）
     * @return string 翻译结果
     */
    public function translate($text, $target = 'en2zh')
    {
        $cacheKey = md5($text . $target); // 生成缓存键

        // 从 APCu 缓存中读取
        if (function_exists('apcu_exists') && apcu_exists($cacheKey)) {
            return apcu_fetch($cacheKey);
        }

        $result = ($target === 'en2zh') ? $this->translateEnToZh($text) : $this->translateZhToEn($text);

        // 将结果存储到 APCu 缓存
        if (function_exists('apcu_store')) {
            apcu_store($cacheKey, $result, 3600); // 缓存1小时
        }

        return $result;
    }

    /**
     * 英文转中文翻译
     * @param string $text 待翻译英文文本
     * @return string 中文翻译结果
     */
    private function translateEnToZh($text)
    {
        $cleanText = preg_replace('/[^a-zA-Z\s]/', '', $text); // 清理非字母字符
        $lowerText = strtolower($cleanText); // 转换为小写

        if (isset($this->dictionaries['en2zh'][$lowerText])) {
            return $this->dictionaries['en2zh'][$lowerText];
        }

        $words = explode(' ', $lowerText); // 分割单词
        $result = [];
        foreach ($words as $word) {
            if (isset($this->dictionaries['en2zh'][$word])) {
                $result[] = $this->dictionaries['en2zh'][$word];
            } else {
                $singular = $this->getSingularForm($word); // 尝试单数形式
                if (isset($this->dictionaries['en2zh'][$singular])) {
                    $result[] = $this->dictionaries['en2zh'][$singular];
                } else {
                    $result[] = $word;
                }
            }
        }
        return implode('', $result);
    }

    /**
     * 中文转英文翻译
     * @param string $text 待翻译中文文本
     * @return string 英文翻译结果
     */
    private function translateZhToEn($text)
    {
        $words = Jieba::cut($text); // 使用jieba分词
        $result = [];
        
        foreach ($words as $word) {
            if (isset($this->dictionaries['zh2en'][$word])) {
                $result[] = $this->dictionaries['zh2en'][$word];
            } else {
                $result[] = $word;
            }
        }
        
        return implode(' ', $result);
    }

    /**
     * 获取单词的单数形式
     * @param string $word 单词
     * @return string 单数形式的单词
     */
    private function getSingularForm($word)
    {
        if (substr($word, -1) === 's') {
            return substr($word, 0, -1);
        }
        return $word;
    }

    /**
     * 应用语法规则
     * @param string $text 文本
     * @param string $target 目标语言
     * @return string 应用规则后的文本
     */
    public function applyGrammarRules($text, $target)
    {
        if ($target === 'en2zh') {
            $text = preg_replace('/\b(\w+)s\b/i', '$1', $text); // 去除英文复数形式
        }
        return $text;
    }

    /**
     * 异步翻译文本
     * @param string $text 待翻译文本
     * @param string $target 目标语言
     * @return Promise 异步翻译结果
     */
    public function asyncTranslate($text, $target)
    {
        $loop = Factory::create();
        $promise = new Promise(function ($resolve, $reject) use ($text, $target) {
            $result = $this->translate($text, $target);
            $resolve($result);
        });

        $loop->run();
        return $promise;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');

    // 初始化翻译服务
    $translator = new TranslationService(
        'localhost',      // 数据库主机
        'translation_db', // 数据库名
        'root',           // 数据库用户名
        'root'            // 数据库密码
    );
    $translator->loadWordDictionary('en2zh');
    $translator->loadWordDictionary('zh2en');
    $response = [];
    switch ($_GET['action']) {
        case 'translate':
            if (isset($_GET['text']) && isset($_GET['target'])) {
                $text = $_GET['text'];
                $target = $_GET['target'];
                $translatedText = $translator->translate($text, $target);
                $response = ['status' => 'success', 'translatedText' => $translatedText];
            } else {
                $response = ['status' => 'error', 'message' => '缺少text或target参数'];
            }
            break;

        default:
            $response = ['status' => 'error', 'message' => '无效的操作'];
            break;
    }

    // 输出结果
    echo json_encode($response);
    exit;
}
