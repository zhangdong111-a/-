(window["webpackJsonp"]=window["webpackJsonp"]||[]).push([["pages-trainType-trainType"],{"0fda":function(t,n,i){"use strict";var a=i("36aa"),e=i.n(a);e.a},"36aa":function(t,n,i){var a=i("494a");"string"===typeof a&&(a=[[t.i,a,""]]),a.locals&&(t.exports=a.locals);var e=i("4f06").default;e("573f27a0",a,!0,{sourceMap:!1,shadowMode:!1})},"47d6":function(t,n,i){"use strict";var a=i("4ea4");Object.defineProperty(n,"__esModule",{value:!0}),n.default=void 0;var e=a(i("ade3")),o={data:function(){var t;return t={type:"线上",type1:"线下",mode:"",id:null,orderid:""},(0,e.default)(t,"type",""),(0,e.default)(t,"lx",""),t},onLoad:function(t){var n=JSON.parse(t.data);this.mode=n.mode,this.id=n.id,this.orderid=n.orderid,this.type=n.type,this.lx=n.id},methods:{submit:function(){var t=this,n={method:this.mode,orderid:this.orderid,cid:this.id,dabao:this.type,lx:this.lx};uni.showModal({title:"是否确认更改？",success:function(i){i.confirm&&t.$sendPost("my/train_methods",n).then((function(t){200==t.code?uni.showToast({title:"申请成功！",success:function(){setTimeout((function(){uni.navigateBack()}),2e3)}}):uni.showToast({title:t.msg,icon:"none"})}))}})}}};n.default=o},"494a":function(t,n,i){var a=i("24fb");n=a(!1),n.push([t.i,'@charset "UTF-8";\r\n/**\r\n * 这里是uni-app内置的常用样式变量\r\n *\r\n * uni-app 官方扩展插件及插件市场（https://ext.dcloud.net.cn）上很多三方插件均使用了这些样式变量\r\n * 如果你是插件开发者，建议你使用scss预处理，并在插件代码中直接使用这些变量（无需 import 这个文件），方便用户通过搭积木的方式开发整体风格一致的App\r\n *\r\n */\r\n/**\r\n * 如果你是App开发者（插件使用者），你可以通过修改这些变量来定制自己的插件主题，实现自定义主题功能\r\n *\r\n * 如果你的项目同样使用了scss预处理，你也可以直接在你的 scss 代码中使用如下变量，同时无需 import 这个文件\r\n */\r\n/* 颜色变量 */\r\n/* 行为相关颜色 */\r\n/* 文字基本颜色 */\r\n/* 背景颜色 */\r\n/* 边框颜色 */\r\n/* 尺寸变量 */\r\n/* 文字尺寸 */\r\n/* 图片尺寸 */\r\n/* Border Radius */\r\n/* 水平间距 */\r\n/* 垂直间距 */\r\n/* 透明度 */\r\n/* 文章场景相关 */uni-page-body[data-v-8368ff74]{background-color:#f2f2f2}.main[data-v-8368ff74]{padding:%?20?%}.main > .title[data-v-8368ff74]{font-size:16px;margin:%?20?% 0;margin-bottom:%?40?%}.main > .title uni-image[data-v-8368ff74]{width:%?50?%;height:%?50?%;vertical-align:middle;margin-right:%?10?%}.main .box[data-v-8368ff74]{background-color:#fff;padding:%?30?% %?20?%;font-size:16px}.main .active[data-v-8368ff74]{background-color:#4087ef;color:#fff}.main .tips[data-v-8368ff74]{margin-top:%?40?%}.main .tips .title[data-v-8368ff74]{color:#f95800}.btn[data-v-8368ff74]{position:fixed;bottom:0;left:0;right:0;width:100%;height:%?150?%;line-height:%?150?%;text-align:center;color:#fff;background-color:#4087ef}body.?%PAGE?%[data-v-8368ff74]{background-color:#f2f2f2}',""]),t.exports=n},"6b46":function(t,n,i){"use strict";i.r(n);var a=i("868b"),e=i("a323");for(var o in e)"default"!==o&&function(t){i.d(n,t,(function(){return e[t]}))}(o);i("0fda");var r,s=i("f0c5"),c=Object(s["a"])(e["default"],a["b"],a["c"],!1,null,"8368ff74",null,!1,a["a"],r);n["default"]=c.exports},"868b":function(t,n,i){"use strict";var a;i.d(n,"b",(function(){return e})),i.d(n,"c",(function(){return o})),i.d(n,"a",(function(){return a}));var e=function(){var t=this,n=t.$createElement,a=t._self._c||n;return a("v-uni-view",{staticClass:"main"},[a("v-uni-view",{staticClass:"title"},[a("v-uni-image",{attrs:{src:i("e70c")}}),t._v("当前培训方式")],1),a("v-uni-view",{class:"线上"==t.mode?"box active":"box",on:{click:function(n){arguments[0]=n=t.$handleEvent(n),t.mode="线上"}}},[t._v("线上")]),a("v-uni-view",{staticClass:"title"},[a("v-uni-image",{attrs:{src:i("e70c")}}),t._v("更改为")],1),a("v-uni-view",{class:"线下"==t.mode?"box active":"box",on:{click:function(n){arguments[0]=n=t.$handleEvent(n),t.mode="线下"}}},[t._v("线下")]),a("v-uni-view",{staticClass:"tips"},[a("v-uni-view",{staticClass:"title"},[t._v("温馨提示")]),a("v-uni-view",[t._v("3.培训方式由线上转")]),a("v-uni-view",[t._v("4.报名截止学员需调整培训方式的请联系客服")])],1),a("v-uni-view",{staticClass:"btn",on:{click:function(n){arguments[0]=n=t.$handleEvent(n),t.submit.apply(void 0,arguments)}}},[t._v("确认申请")])],1)},o=[]},a323:function(t,n,i){"use strict";i.r(n);var a=i("47d6"),e=i.n(a);for(var o in a)"default"!==o&&function(t){i.d(n,t,(function(){return a[t]}))}(o);n["default"]=e.a},e70c:function(t,n,i){t.exports=i.p+"static/img/26.2a156c4b.png"}}]);