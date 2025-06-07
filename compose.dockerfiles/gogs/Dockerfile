FROM gogs/gogs

# 切换到 root 用户以获取安装权限
USER root

RUN sed -i 's/dl-cdn.alpinelinux.org/mirrors.aliyun.com/g' /etc/apk/repositories

# 更新包列表并安装 docker 客户端
# 同时安装 shadow 包，它包含 groupadd 和 usermod 命令，后面会用到
RUN apk update && \
    apk add --no-cache docker-cli shadow

# set timezome
ENV TZ=Asia/Shanghai
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# 切换回 git 用户
USER git

