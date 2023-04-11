<?php

declare(strict_types=1);

namespace fishze;

use Psr\SimpleCache\InvalidArgumentException;
use ZM\Exception\OneBot12Exception;

class What2eat
{

    private array $TIMES = [
        "早上" => [5, 10, "早餐", 0], "今早" => [5, 10, "早餐", 0], "早餐" => [5, 10, "早餐", 0],
        "中午" => [10, 14, "午餐", 1], "午餐" => [10, 14, "午餐", 1],
        "下午茶" => [14, 17, "下午茶", 2],
        "晚上" => [17, 24, "晚餐", 3], "今晚" => [17, 24, "晚餐", 3], "晚餐" => [17, 24, "晚餐", 3],
        "宵夜" => [0, 5, "宵夜", 4],
    ];

    private function GetArrayRand(array $array): string
    {
        return $array[array_rand($array)];
    }

    private function GetNowTimeItem(string $ti): array
    {
        $t = array();
        if (array_key_exists($ti, $this->TIMES)) {
            $t = $this->TIMES[$ti];
        } else {
            foreach ($this->TIMES as $item) {
                if ((date("H") >= $item[0] && date("H") < $item[1])) {
                    $t = $item;
                    break;
                }
            }
        }
        return $t;
    }

    #[\BotCommand(regex: "(今天|早上|中午|下午茶|晚上|宵夜|今早|今晚|早餐|午餐|晚餐)吃(什么|啥)")]
    #[\CommandArgument('time')]
    #[\CommandArgument('what')]
    public function GetWhat2Eat(\BotContext $ctx, \OneBotEvent $event,): void
    {
        if(count($ctx->getParam('.unnamed')) != 0) {
            return;
        }
        $t = $this->GetNowTimeItem($ctx->getParam("time"));
        if (!((date("H") >= $t[0] && date("H") < $t[1]))) {
            $ctx->reply($this->GetArrayRand(["现在不是{$t[2]}的时间哦", "现在还不能吃{$t[2]}哦", "你就那么馋吗？现在还不能吃{$t[2]}哦"]));
        } else {
            try {
                $u = kv("WHAT_TO_EAT")->get("USER_{$event->getUserId()}");
                if ($u == NULL || $u["time"] != date("Y-m-d")) {
                    // 今日未查询过
                    $u = ["have" => array(), "time" => date("Y-m-d")];
                    for ($i = 0; $i < 6; $i++) {
                        $u["have"][$i] = [(int)($i == $t[3]), 0,];
                    }
                } else {
                    // 今日查询过
                    if ($u["have"][$t[3]][0] > 5) {
                        // 问过太多次
                        if ($u["have"][$t[3]][0] < 8) {
                            $ctx->reply($this->GetArrayRand(["今天$t[2]吃得太多了, 歇会再吃吧", "吃那么多$t[2], 你不怕被撑坏吗?", "吃太多$t[2]了, 不能再吃了!", "你是猪猪吗? 吃那么多$t[2]"]));
                            $u["have"][$t[3]][0]++;
                            kv("WHAT_TO_EAT")->set("USER_{$event->getUserId()}", $u);
                        }
                        return;
                    }
                    $u["have"][$t[3]][0]++;
                }
                $m = kv("WHAT_TO_EAT")->get("WHAT_TO_EAT_EATING")["basic_food"];
                if(kv("WHAT_TO_EAT")->get("GROUP_{$event->getGroupId()}") != NULL) {
                    $m = array_merge($m, kv("WHAT_TO_EAT")->get("GROUP_{$event->getGroupId()}"));
                }
                $ctx->reply("建议$t[2]吃 " . $this->GetArrayRand($m));
                kv("WHAT_TO_EAT")->set("USER_{$event->getUserId()}", $u);
            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
                $ctx->reply("WHAT_TO_EAT插件遇到错误, 请查看错误日志");
                ob_dump("WHAT_TO_EAT遇到错误: " . $e->getMessage());
            }
        }
    }

    #[\BotCommand(regex: "(今天|早上|中午|下午茶|晚上|宵夜|今早|今晚|早餐|午餐|晚餐)喝(什么|啥)")]
    #[\CommandArgument('time')]
    #[\CommandArgument('what')]
    public function GetWhat2Drink(\BotContext $ctx, \OneBotEvent $event,): void
    {
        if(count($ctx->getParam('.unnamed')) != 0) {
            return;
        }
        $t = $this->GetNowTimeItem($ctx->getParam("time"));
        if (!((date("H") >= $t[0] && date("H") < $t[1]))) {
            $ctx->reply($this->GetArrayRand(["现在不是{$t[2]}的时间哦", "现在还不能吃{$t[2]}哦", "你就那么馋吗？现在还不能吃{$t[2]}哦"]));
        } else {
            try {
                $u = kv("WHAT_TO_EAT")->get("USER_{$event->getUserId()}");
                if ($u == NULL || $u["time"] != date("Y-m-d")) {
                    // 今日未查询过
                    $u = ["have" => array(), "time" => date("Y-m-d")];
                    for ($i = 0; $i < 6; $i++) {
                        $u["have"][$i] = [0, (int)($i == $t[3])];
                    }
                } else {
                    // 今日查询过
                    if ($u["have"][$t[3]][1] > 5) {
                        // 问过太多次
                        if ($u["have"][$t[3]][1] < 8) {
                            $ctx->reply($this->GetArrayRand(["今天$t[2]喝得太多了, 歇会再喝吧", "喝那么多$t[2], 你不怕被撑坏吗?", "喝太多$t[2]了, 不能再喝了!", "你是猪猪吗? 喝那么多$t[2]"]));
                            $u["have"][$t[3]][1]++;
                            kv("WHAT_TO_EAT")->set("USER_{$event->getUserId()}", $u);
                        }
                        return;
                    }
                    $u["have"][$t[3]][1]++;
                }
                $drinks = kv("WHAT_TO_EAT")->get("WHAT_TO_EAT_DRINKS");
                $brand = $this->GetArrayRand(array_keys($drinks));
                $ctx->reply("建议$t[2]喝 $brand 的 " . $this->GetArrayRand($drinks[$brand]));
                kv("WHAT_TO_EAT")->set("USER_{$event->getUserId()}", $u);
            } catch (\Psr\SimpleCache\InvalidArgumentException $e) {
                $ctx->reply("WHAT_TO_EAT插件遇到错误, 请查看错误日志");
                ob_dump("WHAT_TO_EAT遇到错误: " . $e->getMessage());
            }
        }
    }

    private function getPermission(\OneBotEvent $event) : bool
    {
        $all = config("what-to-eat.admin_qq_id");
        if($all == NULL || !in_array($event->getUserId(), $all)) {
            return false;
        }
        return true;
    }

    /**
     * @throws OneBot12Exception
     * @throws InvalidArgumentException
     */
    #[\BotCommand(match: "/群菜单删除")]
    #[\CommandArgument('param1')]
    public function DelMenu(\BotContext $ctx, \OneBotEvent $event):void
    {
        if($event->getGroupId() != 0 && $this->getPermission($event)) {
            if($ctx->getParam("param1") == NULL || $ctx->getParam("param1") == "") {
                $ctx->reply("请在命令后面加上菜单名称");
                return;
            }
            $nowGroupMenu = kv("WHAT_TO_EAT")->get("GROUP_{$event->getGroupId()}");
            if($nowGroupMenu == NULL) {
                $ctx->reply("该群还没有菜单");
                return;
            }
            if(!in_array($ctx->getParam("param1"), $nowGroupMenu)) {
                $ctx->reply("该菜单不存在");
                return;
            }
            $nowGroupMenu = array_diff($nowGroupMenu, [$ctx->getParam("param1")]);
            kv("WHAT_TO_EAT")->set("GROUP_{$event->getGroupId()}", $nowGroupMenu);
            $ctx->reply("已删除菜单");
        }
    }

    /**
     * @throws OneBot12Exception
     * @throws InvalidArgumentException
     */
    #[\BotCommand(match: "/群菜单添加")]
    #[\CommandArgument('param1')]
    public function AddMenu(\BotContext $ctx, \OneBotEvent $event) : void
    {
        if($event->getGroupId() != 0 && $this->getPermission($event)) {
            if($ctx->getParam("param1") == NULL || $ctx->getParam("param1") == "") {
                $ctx->reply("请在命令后面加上菜单名称");
                return;
            }
            $nowGroupMenu = kv("WHAT_TO_EAT")->get("GROUP_{$event->getGroupId()}");
            if($nowGroupMenu == NULL) {
                $nowGroupMenu = array();
            }
            if(in_array($ctx->getParam("param1"), $nowGroupMenu)) {
                $ctx->reply("该菜单已经存在");
                return;
            }
            $nowGroupMenu[] = $ctx->getParam("param1");
            kv("WHAT_TO_EAT")->set("GROUP_{$event->getGroupId()}", $nowGroupMenu);
            $ctx->reply("添加成功");
        }
    }

    #[\init()]
    public function InitWhat2Eat(): void
    {
        if (config('what-to-eat') === null) {
            logger()->notice('吃什么插件还没有配置文件，正在为你生成，请到 config/what-to-eat.json 填入你的配置项');
            file_put_contents(WORKING_DIR . '/config/what-to-eat.json', json_encode(['admin_qq_id' => array()], JSON_PRETTY_PRINT));
        }
        try {
            kv("WHAT_TO_EAT")->set('WHAT_TO_EAT_DRINKS', json_decode(file_get_contents(dirname(__FILE__) . "/json/drinks.json"), true));
            kv("WHAT_TO_EAT")->set('WHAT_TO_EAT_EATING', json_decode(file_get_contents(dirname(__FILE__) . "/json/eating.json"), true));
        } catch (InvalidArgumentException $e) {
            ob_dump("初始化WHAT_TO_EAT插件时遇到错误: " . $e->getMessage());
        }
    }
}
