## 今天吃什么 What2Eat

## 安装

```
./zhamao plugin:install https://github.com/FishZe/zhamao_plugin_what2eat.git
```

## 使用

```
> 今天吃什么
> 建议宵夜吃 红烧鲤鱼
> 今天喝什么
> 建议宵夜喝 瑞幸 的 摩卡
```

安装后的首次启动后，请前往`config/what-to-eat.json`修改管理QQ

主人可使用该管理QQ在对应群中发送：
```
> /群菜单添加 xxx
> 添加成功

> /群菜单删除 xxx
> 删除成功
```

接收命令: `今天/早上/中午/下午茶/晚上/宵夜/今早/今晚/早餐/午餐/晚餐 + 吃/喝 + 什么/啥`

对于`今天`: 根据当前时间段选择类型
对于其他时间段: 判断是否数据当前时间段


### 改编自 [nonebot_plugin_what2eat](https://github.com/MinatoAquaCrews/nonebot_plugin_what2eat)
