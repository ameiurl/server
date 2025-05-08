<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>错误提示</title>
<meta name="renderer" content="webkit">
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style type="text/css">
::selection{ background-color: #E13300; color: white; }
::moz-selection{ background-color: #E13300; color: white; }
::webkit-selection{ background-color: #E13300; color: white; }
body {
    background-color: #fff;
    font: 13px/20px normal Helvetica, Arial, sans-serif;
    color: #4F5155;
}
a {
    color: #003399;
    background-color: transparent;
    font-weight: normal;
}
h1 {
    color: #999;
    font-size: 28px;
    font-weight: normal;
    margin-top: 0;
    padding:15px;
    line-height: normal;
    overflow: hidden;
}
p {
    margin: 12px 15px 12px 15px;
}
.error {
    width: 600px;
    height: 350px;
    position: absolute;
    top: 0;
    right: 0;
    bottom: 0;
    left: 0;
    margin: auto;
    text-align: center;
}
.error img {
    max-width: 100%;
}
.btn {
    transition: background ease .3s;
    background: #407ae6;
    width: 118px;
    height: 28px;
    text-align: center;
    line-height: 28px;
    display: inline-block;
    font-size: 12px;
    white-space: nowrap;
    cursor: pointer;
    -webkit-border-radius: 2px;
    border-radius: 2px;
    color: #fff;
    border: 1px solid #4c7ae9;
    text-decoration: none;
}
</style>
</head>
<body>
<div class="error">
    <img src="/static/dest/pc/img/common/no_permission.png" alt="页面错误"/>
    <h1>页面错误</h1>
    <h4>错误信息：{$errorMsg}</h4>
    <p><a href="" class="btn">返回首页</a></p>
</div>
</body>
</html>
