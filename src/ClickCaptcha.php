<?php

namespace Jlqiumu\ClickCaptcha;

final class ClickCaptcha
{
    private const CHARS = '口日田目回中十干王丰工土士山出川州米木本末未来大天夫太犬人八个介伞小少尖火水永求主玉全金立音吉古杏呆束东西南北甲由申电曲冉再井开并平半羊年午牛生正反方圆角直弯长短高低宽窄厚薄轻重多早晚新旧冷热明暗亮黑白红绿黄蓝紫灰粉橙青色光影声响静动停走跑跳站坐卧起落升降进退关合分打拍提拉推拿放收发我你他她它的是有在这那谁什么怎很最更都也又还就才刚真假对错好坏美丑香臭甜苦酸辣咸淡硬软湿满空富穷贵贱亲疏远近深且丘丙业丝丞串临乃久之乌乎乐了事于亏云互五亡交亦京今从仓以仝令兄兰共兴其具典兼册冒农冠冬';
    private const FONT = __DIR__ . '/../assets/font.ttf';

    public static function generate(int $clickCount = 4, int $width = 350, int $challengeHeight = 300, int $tipsHeight = 50, int $charSize = 30)
    {
        ($clickCount < 3 || $clickCount > 8 || $width < 100 || $tipsHeight < 20 || $challengeHeight < 100 || $charSize < 8 || $charSize > $challengeHeight || $charSize > $width) && throw new ClickCaptchaException('参数不合法。规则：点击点数量不能小于3不能大于8，图片宽度不能小于100，提示区高度不能小于20，挑战区高度不能小于100，字符尺寸不能大于挑战区高度，字符尺寸不能大于图片宽度。');
        $pool = mb_str_split(self::CHARS);
        shuffle($pool);
        $question = array_map(fn($i) => ['char' => $pool[$i], 'pos' => [mt_rand(0, $width - $charSize), mt_rand($tipsHeight + $charSize, $tipsHeight + $challengeHeight)], 'angle' => (mt_rand(-15, 15) + 360) % 360, 'alpha' => mt_rand(0, 50)], range(0, $clickCount - 1));
        $gd = imagecreatetruecolor($width, $tipsHeight + $challengeHeight);
        imagefill($gd, 0, 0, imagecolorallocate($gd, mt_rand(240, 255), mt_rand(240, 255), mt_rand(240, 255)));
        array_map(fn($i) => imagefttext($gd, rand(10, 30), mt_rand(0, 359), rand(0, $width), rand(0, $tipsHeight + $challengeHeight), imagecolorallocatealpha($gd, mt_rand(0, 255), mt_rand(0, 255), mt_rand(0, 255), mt_rand(80, 127)), self::FONT, $pool[$i]), range($clickCount, $clickCount + 49));
        imagefttext($gd, $tipsHeight * 0.225, 0, 10, $tipsHeight * 0.65, imagecolorallocate($gd, 0, 0, 0), self::FONT, '请依次点击：' . implode('，', array_column($question, 'char')));
        imagefilledrectangle($gd, 0, $tipsHeight - 1, $width, $tipsHeight, imagecolorallocate($gd, 0, 0, 0));
        $answer = array_map(fn($item) => !imagefttext($gd, $charSize * 0.75, $item['angle'], $item['pos'][0], $item['pos'][1], imagecolorallocatealpha($gd, mt_rand(0, 150), mt_rand(0, 150), mt_rand(0, 150), $item['alpha']), self::FONT, $item['char']) ?: [[$item['pos'][0] - 3, $item['pos'][1] - $charSize - 3], [$item['pos'][0] + $charSize + 3, $item['pos'][1] + 3]], $question);
        return ['gd' => $gd, 'jpeg' => ob_start() && imagejpeg($gd) && false ?: ob_get_clean(), 'answer' => $answer];
    }

    public static function verify(array $answer, array $attempt)
    {
        return count($answer) == count($attempt) && array_all(array_map(fn($v1, $v2) => isset($v2[0], $v2[1]) && $v1[0][0] < $v2[0] && $v1[0][1] < $v2[1] && $v1[1][0] > $v2[0] && $v1[1][1] > $v2[1], $answer, $attempt), fn($v) => $v);
    }

    public static function js(string $elementName = 'jlqiumu-click-captcha')
    {
        return str_replace(['<element-name>', "\r", '    ', "\n"], [$elementName, '', '', ' '], file_get_contents(__DIR__ . '/../assets/component.js'));
    }
}