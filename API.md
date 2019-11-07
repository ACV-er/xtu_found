# 失物招领-sky31

> url均为 host 之后的部分(目前host待定)  

## 用户信息部分

### 1. 登录

> 描述: null

* 访问方式: POST

> **url** : `/login`


参数 | 是否必须 |描述 | 后端正则
---|---|---|---
stu_id | 1 | 学号 | `^20[\d]{8,10}$`
password | 1 | 密码 | `^[^\s]{8,20}$`

* 返回示例  

``` JSON
{
    "code":0,
    "status":"成功",
    "data":{
        "user_id":4,
        "nickname":"小白",
        "class":"17自动化三班",
        "qq":"1246009411",
        "wx":null,
        "phone":"14712364598"
    }
}
```
> 头像为上述数据 'upload\avatar\user_id.jpg'

返回参数 | 原因
---|---
0 | 成功
1 | 缺失参数
2 | 账号密码错误
3 | 错误访问
4 | 未知错误
5 | 其他错误

### 2. 用户信息更新  

> 描述: 登陆后使用

* 访问方式: post  

> **url**: `/user/update`

参数 | 是否必须 |描述 | 后端正则
---|---|---|---
nickname | 1 | 昵称 | `^[^\s]{2,16}$`
phone | 0 | 用户手机号 | `^1[0-9]{10}$`
qq | 0 | 用户qq号 | `^[^\s]{2,16}$`
wx | 0 | 用户微信(正则网上找的...) | `^[a-zA-Z]{1}[-_a-zA-Z0-9]{5,19}+$`
class | 0 | 用户班级 | `^[^\s]{5,30}$`
avatar | 0 | 用户头像 | 支持`'jpg', 'jpeg', 'png', 'gif'`  

### 3. 获取用户信息

> 描述: 获取自己信息(登陆后使用)  

* 访问方式: get

> **url**: `/user/info`

* 返回示例  

```JSON
{
    "code":0,
    "status":"成功",
    "data":{
        "user_id":4,
        "nickname":"dhd",
        "class":"17自动化三班",
        "qq":"124600901",
        "wx":"l31312313",
        "phone":"14712364598"
    }
}
```

### 4. 用户帖子获取  

> 描述: 获取自己发布的帖子(登陆后使用)  

* 访问方式: get

> **url**: `/user/lost` `/user/found`  

* 返回示例  

```JSON
{
    "code":0,
    "status":"成功",
    "data":[
        {
            "id":1,
            "user_id":1,
            "title":"0",
            "description":"201705550820",
            "img":null,
            "stu_card":0,
            "card_id":null,
            "address":"二田",
            "date":"2017-12-03",
            "solve":0,
            "created_at":"2019-02-16 12:14:54",
            "updated_at":"2019-02-16 12:14:54"
        },
        {
            "id":2,
            "user_id":1,
            "title":"0",
            "description":"201705550820",
            "img":null,
            "stu_card":0,
            "card_id":null,
            "address":"二田",
            "date":"2017-12-03",
            "solve":0,
            "created_at":"2019-02-16 12:14:56",
            "updated_at":"2019-02-16 12:14:56"
        }
    ]
}
```  

## LAF部分  

### 1. 获取所有lost and found  

> 描述 两个接口  

* 访问方式: get  

> **url**: `/lost` | `/found`  

> 返回示例  

```JSON
{
    "code":0,
    "status":"成功",
    "data":
        {
            "0":
                {
                    "id":1,
                    "user_id":4,
                    "title":"钱包",
                    "description":"红色钱包",
                    "img":"1232333.jpg",
                    "stu_card":0,
                    "card_id":null,
                    "address":"二田",
                    "date":"2017-12-03",
                    "solve":0,
                    "created_at":"2019-02-14 13:12:17",
                    "updated_at":"2019-02-14 13:12:17"
                },
            "1":
                {
                    "id":2,
                    "user_id":4,
                    "title":"耳机",
                    "description":"黑色耳机",
                    "img":"1232333.jpg",
                    "stu_card":0,
                    "card_id":null,
                    "address":"二田",
                    "date":"2017-12-03",
                    "solve":0,
                    "created_at":"2019-02-14 13:12:17",
                    "updated_at":"2019-02-14 13:12:17"
                }
        }
    
}
```

> 部分参数解释

返回参数 | 描述
---|---
id | 帖子id
user_id | 发布者id
stu_card | 是否校园卡
card_id | 如果是校园卡有卡号(暂时写好,如果不保存校园卡信息则该项一直为null)
solve | 是否解决(该接口不会返回以解决的帖子,会一直为0)

### 获取单个帖子详情  

> 描述获取上述列表中的一个  

> 描述: 数据为帖子信息加发布者信息,数据不作描述,参照前面接口  

> **url**: `/lost/{id}` | `/found/{id}`  

> 返回示例  

```JSON
{
    "code":0,
    "status":"成功",
    "data":{
        "id":1,
        "user_id":4,
        "title":"耳机",
        "description":"黑色耳机",
        "img":"1232333.jpg",
        "stu_card":0,
        "card_id":null,
        "address":"二田",
        "date":"2017-12-03",
        "solve":0,
        "created_at":"2019-02-14 13:12:17",
        "updated_at":"2019-02-14 13:12:17"
        "nickname":"dhd",
        "class":"17自动化三班",
        "qq":"1246009411",
        "wx":"l31312313",
        "phone":"14712364598"
    }
}
```  

### 发布和更新lost found(登陆后使用)  

> 描述 除了url 四个接口参数及返回值一致  

> **url** `/submit/lost` `/submit/found` `/update/lost/{id}` `/update/found/{id}`  


参数 | 是否必须 | 描述 |后端正则
---|---|---|---
title | 1 | 标题 | `^[\s\S]{0,100}$`
description | 1 | 描述 | `^[\s\S]{0,200}$`
stu_card | 1 | 是否校园卡(boolean) | `^1|0$`
card_id | 0 | 卡号 | `^20[\d]{8,10}$`
address | 1 | 地点 | `[\s\S]{0,50}`
date | 1 | 日期 | `^[\d]{4}-[\d]{2}-[\d]{2}$`
img | 0 | 提供信息的图片 | 支持`'jpg', 'jpeg', 'png', 'gif'`  

### 标记完成(登陆后使用)  

> 描述 标记该问题已解决  

> **url** `/finish/lost/{id}` `/finish/found/{id}`  


### 未写明的返回码  

> 正常开发时  
* 看status  

> 正常运行时  
* 0 成功 1缺失参数 2 错误访问(不会出现) 
* 6 未登录 7 未完善信息 (这两个会在以下接口出现)  
> `/submit/lost` `/submit/found` `/update/lost/{id}` `/update/found/{id}` `/finish/lost/{id}` 
`/finish/found/{id}` `/user/update` `/user/lost` `/user/found` `/user/info`

