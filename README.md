#weishequ
假设域名为 ：URL = https://huaict.com
            
提交数据  POST方法   目标地址  URL/goods/
更新数据  PUT			URL/goods/:id
获取数据  GET 			URL/goods?id=:id
删除数据  DELETE		URL/goods/:id	


群组店铺模块
群组商店查询 GET      URL/group
验证用户是否在群组 GET              URL/shops?group=[组ID]&user=[用户id]
申请加入群请求 post    URL/shops       参数{  group=[组ID]  , user=[用户id]  }


商城模块

查询所有 GET  /goods?all=1    或者   /goods
查询特定 GET  /goods?条件1=VAL&条件2=VAL2
添加上传商品  POST  /goods    必须参数[ shop_id,user_id,上传图片标识名为 picture,其他参数参考末尾数据结构表 ]
更新数据   PUT  /goods/:id   必须ID参数

提交订单

提交订单  POST /orders     必需参数[  ]


我的商品

查询商品  GET /mygoods?uid=:uid&shop=:shopid


获取地址  GET /district


验证码

获取验证码 

上传接口

支付接口





商品数据结构

`shop_id`  '所属商店ID',

`shop_name`  '所属商店',

`cat_id`  '分类id',

`cat_name` '分类名称',

`extend_cat_id`  '扩展分类id',

`goods_sn`  '商品编号',

`goods_name` T '商品名称',

`click_count`  '点击数',

`brand_id`  '品牌id',

`store_count`  '库存数量',

`comment_count` '商品评论数',

`weight`  '商品重量克为单位',

`market_price`  '市场价',

`shop_price` '本店价',

`cost_price`  '商品成本价',

`keywords`  '商品关键词',

  `goods_remark`  '商品简单描述',
  
  `goods_content`  '商品详细描述',
  
  `godds_img`  '商品上传原始图',
  
  `is_real`  '是否为实物',
  
  `is_on_sale`  '是否上架，0为正常，1为下架,
  
  `is_free_shipping`  '是否包邮0否1是',
  
  `on_time` int(10)  '商品上架时间',
  
  `sort` smallint(4)  '商品排序',
  
  `is_recommend`  '是否推荐',
  
  `is_new`  '是否新品',
  
  `is_hot` '是否热卖',
  
  `last_update`  '最后更新时间',
  
  `goods_type` T '商品所属类型id，取值表goods_type的cat_id',
  `sales_sum`  '商品销量',
  
  `shipping_area_ids`  '配送物流shipping_area_id,以逗号分隔'