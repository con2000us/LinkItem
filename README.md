# 链接导航系统

这个系统用于展示和管理链接列表，使用了 Vue.js 和 PHP 实现。

## cellCSS 自定义设置

每个链接卡片的样式可以通过 `cellCSS` 字段进行自定义。`cellCSS` 是一个 JSON 格式的字符串，包含以下可选属性：

```json
{
  "cardClass": "自定义CSS类名",
  "cardStyle": {
    "属性名": "属性值",
    "borderLeft": "5px solid #颜色代码"
  },
  "numberStyle": {
    "backgroundColor": "#颜色代码",
    "color": "#颜色代码"
  },
  "contentStyle": {
    "backgroundColor": "#颜色代码",
    "--card-bg": "#颜色代码"  // 用于箭头颜色
  },
  "icon": "fas fa-图标名称"  // 使用Font Awesome图标代替数字
}
```

### 示例

以下是一些 `cellCSS` 的示例：

#### 紫色服务器卡片
```json
{
  "cardClass": "custom-card",
  "cardStyle": {
    "borderLeft": "5px solid #9b59b6"
  },
  "numberStyle": {
    "backgroundColor": "#f8f9fa",
    "color": "#9b59b6"
  },
  "contentStyle": {
    "backgroundColor": "#9b59b6",
    "--card-bg": "#9b59b6"
  },
  "icon": "fas fa-server"
}
```

#### 蓝色云存储卡片
```json
{
  "contentStyle": {
    "backgroundColor": "#3498db",
    "--card-bg": "#3498db"
  },
  "numberStyle": {
    "color": "#3498db"
  },
  "icon": "fas fa-cloud"
}
```

#### 绿色数据库卡片
```json
{
  "contentStyle": {
    "backgroundColor": "#2ecc71",
    "--card-bg": "#2ecc71",
    "fontWeight": "bold"
  },
  "numberStyle": {
    "color": "#2ecc71"
  },
  "icon": "fas fa-database"
}
```

## 可用图标

系统使用 [Font Awesome 5](https://fontawesome.com/v5/search) 图标。你可以在 Font Awesome 网站上查找可用的图标名称，并使用 `fas fa-图标名称` 格式指定。

常用图标示例：
- `fas fa-server` - 服务器
- `fas fa-database` - 数据库
- `fas fa-cloud` - 云
- `fas fa-desktop` - 桌面
- `fas fa-laptop` - 笔记本电脑
- `fas fa-mobile-alt` - 手机
- `fas fa-cog` - 设置
- `fas fa-home` - 首页 