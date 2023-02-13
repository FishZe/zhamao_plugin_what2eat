## 今天吃什么 What2Eat

zhamao-frameword >= 3.0.0

PHP >= 8.0

## 安装

```
./zhamao plugin:install https://github.com/FishZe/zhamao_plugin_what2eat.git
```

## 使用

目前只改编了几个小功能

```
> 今天吃什么
> 建议宵夜吃 红烧鲤鱼
> 今天喝什么
> 建议宵夜喝 瑞幸 的 摩卡
```

接收命令: `今天/早上/中午/下午茶/晚上/宵夜/今早/今晚/早餐/午餐/晚餐 + 吃/喝 + 什么/啥)`

对于`今天`: 根据当前时间段选择类型
对于其他时间段: 判断是否数据当前时间段

每天每餐的限制为`3`次, 超出限制后, 会有`2`次的提醒, 之后将不再回复, 该日的其他时间段不受影响.


### 改编自 [nonebot_plugin_what2eat](https://github.com/MinatoAquaCrews/nonebot_plugin_what2eat)
