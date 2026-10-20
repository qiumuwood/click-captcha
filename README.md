# ClickCaptcha

轻量级点选验证码库。

## 效果

ddddocr单题通过率约5%。

## 安装

```bash
composer require jlqiumu/click-captcha
```

需要PHP8.4以上。

## 后端

```php
use Jlqiumu\ClickCaptcha\ClickCaptcha;

$result = ClickCaptcha::generate(); //默认参数即推荐值
$result['jpeg'];
$result['answer']; //校验依据只存服务端

//校验，来自前端e.detail.attempt
ClickCaptcha::verify($result['answer'], $attempt);
```

## 前端

`ClickCaptcha::js()` 返回裸JS依赖字符串。

```html
<jlqiumu-click-captcha img="/Index/genCaptcha" count="4"></jlqiumu-click-captcha>
<script><?= ClickCaptcha::js() ?></script>
```

事件：

- `submit`：`e.detail.attempt` 为 `[[x, y], ...]`，用于`ClickCaptcha::verify()`。
- `refresh`：用户点刷新。

## 字体

使用：[站酷文艺体](https://www.zcool.com.cn/special/zcoolfonts/)。