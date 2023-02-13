<?php

use ZM\Plugin\ZMPlugin;

$plugin = new ZMPlugin();

function What2EatGetNowTimes(string $now): array
{
    $times = [
        "早上" => [5, 10, "早餐", 0], "今早" => [5, 10, "早餐", 0], "早餐" => [5, 10, "早餐", 0],
        "中午" => [10, 14, "午餐", 1], "午餐" => [10, 14, "午餐", 1],
        "下午茶" => [14, 17, "下午茶", 2],
        "晚上" => [17, 24, "晚餐", 3], "今晚" => [17, 24, "晚餐", 3], "晚餐" => [17, 24, "晚餐", 3],
        "宵夜" => [0, 5, "宵夜", 4],
    ];
    if (!array_key_exists($now, $times))
        foreach ($times as $item)
            if ((date("H") >= $item[0] && date("H") < $item[1])) return $item;
    return !array_key_exists($now, $times) ? [0, 24, "今天", 5] : $times[$now];
}

function What2EatCheckMsg(string $msg, string $regex): array
{
    preg_match($regex, $msg, $matches);
    if ($matches[0] != $msg) return [false, ""];
    $time = What2EatGetNowTimes($matches[1]);
    return [(date("H") >= $time[0] && date("H") < $time[1]), $time[2], $time[3]];
}

function What2EatGetArrayRand(array $array): string
{
    return $array[array_rand($array)];
}

function What2EatCheckSum(int $qq, bool $eatType, int $now): array
{
    try {
        $u = kv("WHAT_TO_EAT")->get("USER_$qq");
        if ($u == NULL || $u["time"] != date("Y-m-d")) {
            $u = ["have" => array(), "time" => date("Y-m-d")];
            for ($i = 0; $i < 6; $i++) $u["have"][$i] = [(int)($now == $i && !$eatType), (int)($now == $i && $eatType)];
        } else {
            if (++ $u["have"][$now][$eatType] > 3) return [false, $u["have"][$now][$eatType] > 5, kv("WHAT_TO_EAT")->set("USER_$qq", $u)];
        }
        return [true, kv("WHAT_TO_EAT")->set("USER_$qq", $u)];
    } catch (\Psr\SimpleCache\InvalidArgumentException) {
        return [false, true];
    }
}

$plugin->addBotCommand(BotCommand::make('what2eat', regex: "(今天|早上|中午|下午茶|晚上|宵夜|今早|今晚|早餐|午餐|晚餐)吃(什么|啥)")->on(function (OneBotEvent $event, BotContext $ctx) {
    $a = What2EatCheckMsg($event->getMessageString(), "/(今天|早上|中午|下午茶|晚上|宵夜|今早|今晚|早餐|午餐|晚餐)吃(什么|啥)/u");
    if (!$a[0]) {
        $ctx->reply($a[1] == "" ? "" : "现在不是$a[1]的时间哦");
    } else {
        $c =  What2EatCheckSum($event->getUserId(), 0, $a[2]);
        if (!$c[0]) {
            if (!$c[1]) $ctx->reply(What2EatGetArrayRand(["今天$a[1]吃得太多了, 歇会再吃吧", "吃那么多$a[1], 你不怕被撑坏吗?", "吃太多$a[1]了, 不能再吃了!", "你是猪猪吗? 吃那么多$a[1]"]));
        } else {
            $ctx->reply("建议$a[1]吃 " . What2EatGetArrayRand(kv("WHAT_TO_EAT")->get("WHAT_TO_EAT_EATING")["basic_food"]));
        }
    }
}));

$plugin->addBotCommand(BotCommand::make('what2drink', regex: "(今天|早上|中午|下午茶|晚上|宵夜|今早|今晚|早餐|午餐|晚餐)喝(什么|啥)")->on(function (OneBotEvent $event, BotContext $ctx) {
    $a = What2EatCheckMsg($event->getMessageString(), "/(今天|早上|中午|下午茶|晚上|宵夜|今早|今晚|早餐|午餐|晚餐)喝(什么|啥)/u");
    if (!$a[0]) {
        $ctx->reply($a[1] == "" ? "" : "现在不是$a[1]的时间哦");
    } else {
        $c =  What2EatCheckSum($event->getUserId(), 1, $a[2]);
        if (!$c[0]) {
            if (!$c[1]) $ctx->reply(What2EatGetArrayRand(["今天$a[1]喝得太多了, 歇会再喝饮料吧", "$a[1]喝那么多, 你不怕被撑坏吗?", "$a[1]喝的太多了, 不能再喝了!", "你是猪猪吗? 喝那么多$a[1]"]));
        } else {
            $drinks = kv("WHAT_TO_EAT")->get("WHAT_TO_EAT_DRINKS");
            $brand = What2EatGetArrayRand(array_keys($drinks));
            $ctx->reply("建议$a[1]喝 $brand 的 " . What2EatGetArrayRand($drinks[$brand]));
        }
    }
}));

$plugin->onPluginLoad(function () {
    kv("WHAT_TO_EAT")->set('WHAT_TO_EAT_DRINKS', json_decode(file_get_contents(dirname(__FILE__) . "/json/drinks.json"), true));
    kv("WHAT_TO_EAT")->set('WHAT_TO_EAT_EATING', json_decode(file_get_contents(dirname(__FILE__) . "/json/eating.json"), true));
});

return $plugin;