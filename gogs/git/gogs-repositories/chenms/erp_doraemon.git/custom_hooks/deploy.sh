#!/bin/sh

# 切换到仓库的根目录，确保脚本环境一致
# 如果切换失败，脚本将退出
cd /data/git/gogs-repositories/chenms/erp_doraemon.git || exit 1

# --- 指定命令的绝对路径 ---
GIT_CMD="/usr/bin/git" # 替换成你用 which 查到的实际路径
DOCKER_CMD="/usr/bin/docker" # 替换成你用 which 查到的实际路径

# --- 定义日志和路径变量 ---
LOG_FILE="./deploy.log" # 使用相对路径，因为我们已经 cd 了
TMP_DEPLOY_DIR="/tmp/erp_doraemon_deploy"

# --- 记录日志 ---
echo "==========================================" >> "$LOG_FILE"
echo "Deployment started at $(date)" >> "$LOG_FILE"
echo "==========================================" >> "$LOG_FILE"

# --- 部署逻辑，同时捕获所有输出到日志文件 ---
{
  echo "--- Cleaning up temporary directory ---"
  rm -rf $TMP_DEPLOY_DIR
  
  echo "--- Checking out latest code from master branch ---"
  $GIT_CMD --work-tree=$TMP_DEPLOY_DIR --git-dir="." checkout master -f

  echo "--- Copying files to myphp container ---"
  $DOCKER_CMD cp "$TMP_DEPLOY_DIR/." myphp:/var/www/html/erp_doraemon

  echo "--- Syncing files with rsync inside myphp container ---"
  $DOCKER_CMD exec myphp rsync -av --delete /var/www/html/erp_doraemon/ /var/www/html/erp_doraemon_test/

  echo "--- Cleaning up temporary directory again ---"
  rm -rf $TMP_DEPLOY_DIR

} >> "$LOG_FILE" 2>&1

# --- 记录部署结束日志 ---
echo "Deployment finished at $(date)" >> "$LOG_FILE"
echo "" >> "$LOG_FILE"

exit 0
