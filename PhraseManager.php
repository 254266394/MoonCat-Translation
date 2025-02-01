<?php
/**
 * 月半猫短语管理器
 * 作者：芸芸众生
 * 月半猫出品
 * 日期：2025年2月1日
 * 说明：向数据库添加短语的，支持双向保存、日志记录和异步处理
 * 注意：。
 */

class PhraseManager{
    private $dbConnection;
    public function __construct(
        $dbHost = 'localhost',
        $dbName = 'translation_db',
        $dbUser = 'root',
        $dbPass = 'password'
    ) {
        $this->initializeDatabase($dbHost, $dbName, $dbUser, $dbPass);
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
     * 添加短语到数据库
     * @param string $source 源文本
     * @param string $target 目标文本
     */
    public function addPhraseToDatabase($source, $target)
    {
        // 双向保存
        $this->savePhraseToDatabase($source, $target, 'en2zh');
        $this->savePhraseToDatabase($target, $source, 'zh2en');
    }

    /**
     * 保存短语到数据库
     * @param string $source 源文本
     * @param string $target 目标文本
     * @param string $type 词典类型（'en2zh'、'zh2en'）
     */
    private function savePhraseToDatabase($source, $target, $type)
    {
        try {
            // 检查是否已存在
            $stmt = $this->dbConnection->prepare("
                SELECT id FROM dictionaries 
                WHERE translation_type = :type AND source_text = :source
            ");
            $stmt->execute([':type' => $type, ':source' => $source]);
            
            if ($stmt->fetch()) {
                $this->log("[重复跳过] $source|$target -> $type");
                return;
            }

            // 插入新记录
            $stmt = $this->dbConnection->prepare("
                INSERT INTO dictionaries 
                (translation_type, source_text, target_text) 
                VALUES (:type, :source, :target)
            ");
            $stmt->execute([
                ':type' => $type,
                ':source' => $source,
                ':target' => $target
            ]);

            $this->log("[写入成功] $source|$target -> $type");
        } catch (PDOException $e) {
            throw new Exception("数据库操作失败：" . $e->getMessage());
        }
    }

    /**
     * 记录日志
     * @param string $message 日志内容
     */
    private function log($message)
    {
        $logFile = 'logs/phrase_manager.log'; // 日志文件路径
        $timestamp = date('Y-m-d H:i:s');     // 时间戳
        file_put_contents($logFile, "[$timestamp] $message\n", FILE_APPEND);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action'])) {
    header('Content-Type: application/json');
    
    try {
        if ($_GET['action'] === 'addPhrase') {
            // 参数校验
            $requiredParams = ['source', 'target'];
            foreach ($requiredParams as $param) {
                if (!isset($_GET[$param])) {
                    throw new InvalidArgumentException("缺少必要参数: $param");
                }
            }
            //过滤
            $source = trim($_GET['source']);
            $target = trim($_GET['target']);
            if (empty($source) || empty($target)) {
                throw new InvalidArgumentException("短语不能为空");
            }
            if (strpos($source, '|') !== false || strpos($target, '|') !== false) {
                throw new InvalidArgumentException("短语包含非法字符 '|'");
            }

            // 发送响应后继续处理
            echo json_encode(['status' => 'success', 'message' => '请求已接收']);
            
            if (function_exists('fastcgi_finish_request')) {
                fastcgi_finish_request();
            }

            $phraseManager = new PhraseManager(
                'localhost',      // 数据库主机
                'translation_db', // 数据库名
                'root',           // 数据库用户名
                'root'            // 数据库密码
            );
            $phraseManager->addPhraseToDatabase($source, $target);
            
            exit;
        }
        throw new InvalidArgumentException("无效操作类型");
    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode([
            'status' => 'error',
            'message' => $e->getMessage()
        ]);
        exit;
    }
}
?>