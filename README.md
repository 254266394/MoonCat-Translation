# MoonCat-Translation

## 简介
月半猫翻译是一个中英文互译工具，支持缓存、数据库词典加载、分词及异步翻译。项目基于 PHP 开发，使用了 [Fukuball/jieba](https://github.com/Fukuball/jieba-php) 进行中文分词，并通过 APCu 实现缓存优化。

## 功能
- 中英文互译
- 支持缓存（APCu）
- 数据库词典加载
- 分词支持（中文）
- 异步翻译
- 短语管理（双向保存、日志记录）

## 安装

### 环境要求
- PHP 7.4 或更高版本
- MySQL 数据库
- APCu 扩展
- Composer
- predis/predis
- Fukuball/jieba

### 安装步骤
1. 克隆仓库：
   ```bash
   git clone https://github.com/254266394/mooncat-translation.git
   cd mooncat-translation
   ```
2. 使用方法：
**目录**：
logs/
├phrase_manager.log
MoonCatTranslation.php
index.php
PhraseManager.php
MoonCatTranslation.sql

   **MoonCatTranslation.php 中英文互译**
   **PhraseManager.php 向数据库添加短语**
   **index.php 简单的界面**
   **MoonCatTranslation.sql 需要导入数据库中**
   **添加数据库：** 分别在MoonCatTranslation.php和PhraseManager.php中添加数据库。
   ```php
   $translator = new MoonCatTranslation(
   'localhost',      // 数据库主机
   '', // 数据库名
   '',           // 数据库用户名
   ''            // 数据库密码
   );
   ```
**翻译**
   ```bash
   GET /MoonCatTranslation.php?action=translate&text=Hello World&target=en2zh
   ```
  参数：text=要翻译的文本
  参数：target=翻译为目标文本(必选：zh2en或en2zh 分别是中文转英文、英文转中文)
  **响应示例：**
  ```json
  {
  "status": "success",
  "translatedText": "你好，世界"
  }
  ```
**添加短语**
  ```bash
  GET /PhraseManager.php?action=addPhrase&source=world&target=世界
  ```
  参数：source=短语英文
  参数：target=短语中文
  **响应示例 ：**
  ```json
  {
    "status": "success",
    "message": "请求已接收"
   }
  ```
  
  ### 联系方式
  **作者：芸芸众生**
  **GitHub：https://github.com/254266394 **
  **Gitee：https://gitee.com/lonebright **

  ## License
   本项目采用 [MIT License](LICENSE) 协议。
