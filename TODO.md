## TODO:

- [x] 人员信息排序查看
- [x] 查询人员信息
- [x] 人员登记增加登记的时间、设备、IP
- [x] 各页面可以设置隐藏
- [x] bootstrap hover dropdown
- [ ] 取年级列表增加过滤已删除人员
- [ ] 设置哪些人可以不安排值班
- [ ] 管理员给人员设置职位标签
- [ ] 设置Tips内容
- [ ] 登记内容增加验证
- [ ] shuffle开关，随机排序
- [ ] 多租户管理
- [ ] 版本自动升级 http://sogo6.iteye.com/blog/691530
- [ ] 值班表可在配置页面更改
- [ ] 打印导出功能
- [ ] 增加微信墙等活动功能
- [ ] 代码优化，要优雅不要污
- [ ] 编写使用手册


## BUG Fix:

- [x] 删除人员附带删除人员年级，解决办法：读取人员年级时，增加status的选择读取，原因：删除人员只是更改status
- [x] 空值班段导致500错误，解决：万能的@符号
- [x] 搜索时课表UI出现BUG