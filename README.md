几个注意的点：

所有命令文件都是在www用户，也就是apache或者nginx下执行。
所以在服务器上部署时候，需要做的内容有：
1.保证代码目录下的所有内容为www用户组。chown -R www:www /code
2.保证部署脚本也是www用户组。chown -R www:www /code
3.保证xxx.sh脚本为777权限。
4.需要将www用户组的sshkey加到代码仓库中，生成方式：sudo -u www ssh-keygen -t rsa -C "your@mail.com"

Debug小技巧：
可以现在服务器上测试是否可行，测试方式如下：
sudo -u www php xxx.php 会提示接受gitlab的rsa，输入yes即可



GitLab Web Hook
===============

This is a simple PHP script to execute arbitrary shell commands each time
someone pushes something into a GitLab repository.

Installation
------------

This script should be placed within the web root of your desired deploy
location. The GitLab repository should then be configured to call it for the
"Push events" trigger via the Web Hooks settings page.

Each time this script is called, it executes a hook shell script and logs all
output to the log file.

This hook uses php's `exec()` function, so make sure it can be executed.
See [PHP manual](http://php.net/manual/function.exec.php) for more info

Configuration
-------------

The hook pulls the code for the current branch from the upstream remote branch
by default. You can configure it to execute any other shell commands by
modifying the `.hooks/gitlab-webhook-push.sh` script.

### PHP configuration

The following PHP configuration options are available via editing the
`gitlab-webhook-push.php` script file:

#### `$hookfile`

*REQUIRED*

A shell script file to execute. Defaults to `".hooks/gitlab-webhook-push.sh"`:

```php
$hookfile = ".hooks/gitlab-webhook-push.sh";
```

#### `$logfile`

*REQUIRED*

A log file for both the shell script and the PHP script. Defaults to
`".hooks/gitlab-webhook-push.log"`:

```php
$logfile = ".hooks/gitlab-webhook-push.log";
```

#### `$password`

*RECOMMENDED*

A password, which should be passed via GET param to prevent unauthorized web
hook execution. Not set by default:

```php
$password = 'MY_STRONG_PASSWORD';
```


To pass in a password, the url to your web hook in the GitLab settings should
look like this:

```
http://example.com/gitlab-webhook-push.php?p=MY_STRONG_PASSWORD
```

#### `$ref`

*OPTIONAL*

A Ref to filter events by. Can be either string or an array of strings:

```php
$ref = "refs/heads/master";

// OR

$ref = array("refs/heads/master", "refs/heads/develop");
```


Does not actually support Refspec, the refs are expected to match exactly.

For more info on the subject of Refspec and refs, see
[this page from Git Internals book](http://git-scm.com/book/en/Git-Internals-The-Refspec).

### Shell configuration

It is recommended to move the shell script (and the logs) outside of the web
root. For this to work PHP should be able to still write the log file and
execute the shell script, and both `$hookfile` and `$logfile` settings should
point to the new location. The shell script should also be modified to
accomodate for the new location.

Let's assume your shell script was moved to a directory which contains the web
root. The directory structure looks something like this:

```
.
+-- htdocs
|   +-- gitlab-webhook-push.php
+-- gitlab-webhook-push.sh
```


Your shell script should be modified:

```sh
# This line is no longer a correct line
cd .. > /dev/null &

# Instead, it should look like this:
cd htdocs > /dev/null &
```


Your PHP configuration would look something like this:

```php
$hookfile = "../gitlab-webhook-push.sh";
$logfile  = "../gitlab-webhook-push.log";
```
