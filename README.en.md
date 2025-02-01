# MoonCat-Translation

## Introduction
MoonCat Translate is a Chinese-English translation tool supporting caching,dictionary loading from databases,word segmentation,and asynchronous translation.It is developed in PHP,using [Fukuball/jieba](https://github.com/Fukuball/jieba-php) for Chinese word segmentation and APCu for cache optimization.

## Features
- Chinese-English bidirectional translation
- Cache support(APCu)
- Dictionary loading from database
- Word segmentation support(Chinese)
- Asynchronous translation
- Phrase management(bi-directional saving,logging)

### Environment Requirements
- PHP 7.4 or higher
- MySQL database
- APCu extension
- Composer
- predis/predis
- Fukuball/jieba

### Steps
1. Clone the repository:
   ```bash
   git clone https://github.com/yourusername/mooncat-translation.git
   cd mooncat-translation
   ```
2. Usage:
**Directory:**
logs/
├phrase_manager.log
MoonCatTranslation.php
index.php
PhraseManager.php
MoonCatTranslation.sql

   **MoonCatTranslation.php Chinese-English Translation**
   **PhraseManager.php Add phrases to the database**
   **index.php Simple interface**
   **MoonCatTranslation.sql Need to be imported into the database**
   **Add database:** Add the database in MoonCatTranslation.php and PhraseManager.php.
   ```php
   $translator = new MoonCatTranslation(
   'localhost',      // 数据库主机
   '', // 数据库名
   '',           // 数据库用户名
   ''            // 数据库密码
   );
   ```
**Translate**
   ```bash
   GET /MoonCatTranslation.php?action=translate&text=Hello World&target=en2zh
   ```
  Parameter: text=(Text to be translated)
  Parameter: target=(Translation target(Required: zh2en for Chinese to English, en2zh for English to Chinese))
  **Response example:**
  ```json
  {
  "status": "success",
  "translatedText": "你好，世界"
  }
  ```
**Add phrases**
  ```bash
  GET /PhraseManager.php?action=addPhrase&source=world&target=世界
  ```
  Parameter: source=English phrase
  Parameter: target=Chinese phrase
  **Response example:**
  ```json
  {
    "status": "success",
    "message": "请求已接收"
   }
  ```
  
  ### Contact Information
  **Author:芸芸众生**
  **GitHub：https://github.com/254266394 **
  **Gitee：https://gitee.com/lonebright **

## License
   This project adopts the [MIT LICENSE] (License) protocol.