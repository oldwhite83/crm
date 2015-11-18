<?php

/**
 * 过滤空数组.
 */
function __handleCondition($data)
{
    foreach ($data as $k => $v) {
        if (!$v) {
            unset($data[$k]);
        }
    }

    return $data;
}

//gulp打包CDN
function elixirCDN($file)
{
    $cdn = rtrim(Config::get('common.cdn', ''), '/');
    try {
        $path = $cdn.elixir($file);
    } catch (\Exception $e) {
        $path = $cdn.'/'.$file;
    }

    return $path;
}

//普通CDN
function CDN($file)
{
    $cdn = rtrim(Config::get('common.cdn', ''), '/');
    $path = $cdn.'/'.$file;

    return $path;
}

//显示时间
function displayTime($time)
{
    if ($time <= 0) {
        return '';
    }
    if ($time > 3600) {
        $m = round($time % 3600 / 60);
        $h = floor($time / 3600);

        return $h.'小时'.$m.'分';
    }

    $m = round($time / 60);

    return $m.'分';
}

function weekDayStr($datatime)
{
    $week = ['日', '一', '二', '三', '四', '五', '六'];

    return '周'.$week[date('w', strtotime($datatime))];
}

function displayWeekTime($datatime)
{
    $time = strtotime($datatime);

    return date('Y-m-d', $time).' '.weekDayStr($datatime).' '.date('H:i', $time);
}

//作业剩余时间
function homeworkCorrectLastTime($homeworkRelation)
{
    if ($homeworkRelation->status == 1) {
        return '-';
    }

    $rob_at = $homeworkRelation->rob_at;
    $lastTime = intval(Config::get('common.homework_limit_time')) + strtotime($rob_at) - time();

    return displayTime($lastTime);
}

//所有作业状态
function allHomeworkStatus($homework)
{
    if ($homework['status'] == 1) {
        return ['已批改', 'success'];
    }
    if ($homework['rob_status'] == 1) {
        return ['已抢单', 'info'];
    }
    if ($homework['status'] == -2) {
        return ['逾期', 'danger'];
    }
    if ($homework['status'] == -1) {
        return ['废弃', 'default'];
    }
    $expect_correction_at = strtotime($homework['expect_correction_at']);
    if ($expect_correction_at - time() <= intval(Config::get('common.homework_limit_time'))) {
        return ['紧急', 'danger'];
    }

    return ['未批改', 'danger'];
}

//批改作业状态
function homeworkStatus($homework)
{
    if ($homework['status'] == 1) {
        return ['已批改', 'success'];
    }
    if ($homework['rob_status'] == 1) {
        return ['已抢单', 'info'];
    }
    if ($homework['status'] == -2) {
        return ['逾期', 'danger'];
    }
    if ($homework['status'] == -1) {
        return ['废弃', 'default'];
    }

    return ['未批改', 'danger'];
}

//我的作业状态
function myHomeworkStatus($homework)
{
    if ($homework['status'] == 1) {
        return ['已批改', 'success'];
    }
    if ($homework['status'] == -2) {
        return ['逾期', 'danger'];
    }
    if ($homework['status'] == -1) {
        return ['废弃', 'default'];
    }

    return ['未批改', 'danger'];
}

//作业价格
function homeworkMoney($homework)
{
    if ($homework->status == 1) {
        return moneyFormat($homework->homework->money, $homework->multiple);
    }

    if (empty($homework->clazz->homeworkDouble)) {
        return moneyFormat($homework->homework->money);
    }
    $holidays = collect($homework->clazz->homeworkDouble);
    //已抢单按抢单时间计算
    if ($homework->rob_status == 1) {
        $time = strtotime($homework->rob_at);
    } else {
        $time = time();
    }

    $holiday = $holidays->search(function ($item, $key) use ($time) {
        return ((strtotime($item->start_at) <= $time)
             and
            (strtotime($item->end_at) >= $time));
    });
    if ($holiday === false) {
        return moneyFormat($homework->homework->money);
    }

    return moneyFormat($homework->homework->money, $holidays[$holiday]->multiple);
}

//作业评分
function homeworkScore($homework)
{
    if ($homework['status'] == 1) {
        return $homework['score'];
    }

    return '-';
}

//作业剩余批改时间
function homeworkLastTime($homework)
{
    $expect_correction_at = strtotime($homework->expect_correction_at);

    return displayTime($expect_correction_at - time());
}

//钱包明细说明
function walletDetail($detail)
{
    if ($detail->status == 1) {
        try {
            return '批改了 '.$detail->uhRelation->student->truename.' V'.$detail->uhRelation->version.' '.$detail->uhRelation->homework->title;
        } catch (\Exception $e) {
            return '未知';
        }
    } elseif ($detail->status == 5) {
        return $detail->description;
    }

    return '提现';
}

//显示金额
function moneyFormat($number, $multiple = '')
{
    if (empty($multiple) || $multiple == 1) {
        return number_format($number, 2, '.', ',');
    } else {
        return number_format($number, 2, '.', ',').' <font color=red>X'.$multiple.'</font>';
    }
}

//打分显示
function dimensionScore($score)
{
    $tips = '';
    if ($score <= 11) {
        $tips = '不及格';
        $class = 'd';
    } elseif ($score <= 15) {
        $tips = '及格';
        $class = 'c';
    } elseif ($score <= 18) {
        $tips = '良好';
        $class = 'b';
    } elseif ($score <= 20) {
        $tips = '优秀';
        $class = 'a';
    }

    return $score.'分 <span class="badge badge-roundless badge-custom-'.$class.'">'.$tips.'</span>';
}

//文件后缀
function attachSuffix($filename)
{
    return strtolower(pathinfo($filename, PATHINFO_EXTENSION));
}

//文件名称
function attachName($path)
{
    return pathinfo($path, PATHINFO_BASENAME);
}

//UC加密
function ucAuthcode($string, $operation = 'DECODE', $key = 'jkxy!@#$123', $expiry = 0)
{
    $ckey_length = 4;

    $key = md5($key);
    $keya = md5(substr($key, 0, 16));
    $keyb = md5(substr($key, 16, 16));
    $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

    $cryptkey = $keya.md5($keya.$keyc);
    $key_length = strlen($cryptkey);

    $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string;
    $string_length = strlen($string);

    $result = '';
    $box = range(0, 255);

    $rndkey = array();
    for ($i = 0; $i <= 255; ++$i) {
        $rndkey[$i] = ord($cryptkey[$i % $key_length]);
    }

    for ($j = $i = 0; $i < 256; ++$i) {
        $j = ($j + $box[$i] + $rndkey[$i]) % 256;
        $tmp = $box[$i];
        $box[$i] = $box[$j];
        $box[$j] = $tmp;
    }

    for ($a = $j = $i = 0; $i < $string_length; ++$i) {
        $a = ($a + 1) % 256;
        $j = ($j + $box[$a]) % 256;
        $tmp = $box[$a];
        $box[$a] = $box[$j];
        $box[$j] = $tmp;
        $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
    }

    if ($operation == 'DECODE') {
        if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) {
            return substr($result, 26);
        } else {
            return '';
        }
    } else {
        return $keyc.str_replace('=', '', base64_encode($result));
    }
}

//发送邮件
function __sendemail(array $to, $subject, $content = [], $template = null, $extra = [])
{
    if (empty($to) or empty($subject)) {
        return false;
    }
    $template = $template ?: 'notification';

    $toUser = [];
    foreach ($to as $user) {
        if (empty($user)) {
            continue;
        }

        if (is_array($user)) {
            $address = !empty($user['address']) ? $user['address'] : null;
            $name = !empty($user['name']) ? $user['name'] : null;
        } else {
            $address = $user->address;
            $name = $user->name;
        }

        if (empty($address)) {
            continue;
        }
        $toUser[] = [$address, $name];
    }
    if (empty($toUser)) {
        return false;
    }

    return Mail::queue('email.'.$template, $content, function ($message) use ($toUser, $subject, $extra) {
        $title = Config::get('app.name');
        $message->subject('「'.$title.'」'.$subject);
        foreach ($toUser as $user) {
            $message->to($user[0], $user[1]);
        }
        if (!empty($extra['cc'])) {
            foreach ($extra['cc'] as $ccUser) {
                if (empty($ccUser)) {
                    continue;
                }

                if (is_array($ccUser)) {
                    $address = !empty($ccUser['address']) ? $ccUser['address'] : null;
                    $name = !empty($ccUser['name']) ? $ccUser['name'] : null;
                } else {
                    $address = $ccUser->address;
                    $name = $ccUser->name;
                }

                if (empty($address)) {
                    continue;
                }
                $message->cc($address, $name);
            }
        }
    });
}

function __assistantApplyJobs($apply, array $jobs)
{
    if ($apply->apply_type == 1) {
        return $apply->direction;
    }
    $job_ids = $apply->jobs->lists('job_id')->toArray();
    $res = [];
    foreach ($job_ids as $value) {
        if (!empty($jobs[$value])) {
            $res[] = $jobs[$value];
        }
    }

    if (empty($res)) {
        return '-';
    }

    return implode(', ', $res);
}

function __trainStatusPlToTable($pl_status)
{
    $status = Config::get('plvideo.status');

    $status_id = 1;
    $status_id = $pl_status == 51 ? -2 : $status_id;
    $status_id = $pl_status < 0 ? -1 : $status_id;
    $status_id = $pl_status >= 10 && $pl_status <= 26 ? 0 : $status_id;
    $status_id = $pl_status == 60 || $pl_status == 61 ? 2 : $status_id;

    if (empty($status[$status_id])) {
        return false;
    }

    return $status[$status_id]['id'];
}

/**
 *	@desc 将 "00:01:02" 转换成秒数。一般用于求视频的总秒数。 +8hour表示中国时区
 */
function __dateToTime($his = '')
{
    $second = 0;
    if ($his) {
        $second = strtotime("1970-01-01 {$his} +8hour");
    }

    return $second;
}

function __tryoutTaskStatus($status)
{
    // 状态 1：未开始，2：待初审，3：初审未通过，4：初审通过(待复审)，5：复审未通过，6：复审通过
    switch ($status) {
        case '-1':
            $color = 'default';
            $message = '废弃';
            break;
        case '1':
            $color = 'default';
            $message = '待提交';
            break;
        case '2':
            $color = 'default';
            $message = '待初审';
            break;
        case '3':
            $color = 'danger';
            $message = '初审未通过';
            break;
        case '4':
            $color = 'default';
            $message = '待复审';
            break;
        case '5':
            $color = 'danger';
            $message = '复审未通过';
            break;
        case '6':
            $color = 'success';
            $message = '复审通过';
            break;
        default:
            $color = 'default';
            $message = '未知';
            break;
    }

    return [$color,$message];
}

function __approvalStatus($approval)
{
    if ($approval->time == 1) {
        $time = '初审';
    } else {
        $time = '复审';
    }

    if ($approval->status == 1) {
        $status = '通过';
    } else {
        $status = '不通过';
    }

    return $time.$status;
}

function __paperStatus($status)
{
    // 10:选题 - 待审核,20:选题 - 审核不通过,30:选题 - 审核通过,40:提交作品 - 待审核,50:提交作品 - 审核不通过,60:提交作品 - 审核通过
    $color = '';
    switch ($status) {
        case '10':
            $color = 'danger';
            $message = '选题 - 待审核';
            break;
        case '20':
            $message = '选题 - 审核不通过';
            break;
        case '30':
            $message = '选题 - 审核通过';
            break;
        case '40':
            $color = 'danger';
            $message = '提交作品 - 待审核';
            break;
        case '50':
            $message = '提交作品 - 审核不通过';
            break;
        case '60':
            $message = '提交作品 - 审核通过';
            break;

        default:
            $color = 'danger';
            $message = '未知';
            break;
    }

    return [$color,$message];
}

/**
 *  @desc 将秒数转换成时间
 */
function minuteSecond($seconds)
{
    $minute = floor($seconds / 60);
    $seconds = $seconds - $minute * 60;
    $minute = fillZero($minute);
    $seconds = fillZero($seconds);

    return $minute.':'.$seconds;
}
function fillZero($num, $repeat = 2, $repeat_stuff = '0')
{
    $num = (int) $num;
    $num_len = strlen($num);
    $num_repeat = $repeat - $num_len ? $repeat - $num_len : 0;
    $num = str_repeat($repeat_stuff, $num_repeat).$num;

    return $num;
}

/**
 *	@desc	sd的视频文件名转换为hd的文件名
 *			"/unity3d/course_1296/01/video/c1296b_02_h264_sd_960_540.mp4"   --->   "/unity3d/course_1296/01/video/c1296b_02_h264_hd_960_540.mp4"
 */
function videoPathSdToHd($sd_video_path = '')
{
    $hd_video_path = '';
    if ($sd_video_path) {
        $hd_video_path = str_replace('_h264_sd_960_540', '_hd_h264_1280_720', $sd_video_path);
    }

    return $hd_video_path;
}

/**
 *	@desc	解析通过coreAPI接口返回的cdn链接，返回文件名
 *			http://cv3.jikexueyuan.com/201511031030/4615c333d5116f03f2319c4f883a937d/android/course_frame_animation/01/video/c77_01_hd_h264_1280_720.mp4
 *		TO -> android/course_frame_animation/01/video/c77_01_hd_h264_1280_720.mp4  c77_01_hd_h264_1280_720.mp4
 */
function parsingCdnurlToFileName($cdn_video_url)
{
    $file_name = '';
    if ($cdn_video_url) {
        $path_info = pathinfo($cdn_video_url);
        $file_name = $path_info['basename'];
    }

    return $file_name;
}

/**
 *	@desc	用video_info表里存的视频文件名与获取到的加密cdn链接进行对比，如果不一致那么返回false。
 *
 *	@return bool
 */
function checkCdnVSFile($file_name, $cdn_video_url)
{
    $result = false;
    if ($file_name && $cdn_video_url) {
        $cdn_file_name = parsingCdnurlToFileName($cdn_video_url);
        if ($file_name == $cdn_file_name) {
            $result = true;
        }
    }

    return $result;
}

//概要设计文档下载的文件名：毕业设计 - {学员真实姓名} - 概要设计Vn.xx
function __displayTopicDownloadUrl($topic, $student)
{
    return $topic->doc_url.'?attname='.urlencode('毕业设计 - '.$student->truename.' - 概要设计V'.$topic->version.'.'.attachSuffix($topic->doc_url));
}

//作品压缩包下载的文件名：毕业设计 - {学员真实姓名} - 作品Vn.xx
function __displayWorkDownloadUrl($work, $student)
{
    return $work->work_url.'?attname='.urlencode('毕业设计 - '.$student->truename.' - 作品V'.$work->version.'.'.attachSuffix($work->work_url));
}
/**
 *	@desc	奖励设置的规则检测
 *			唯一合法规则：[1 ~ 4, 5 ~ 10, 11 ~ 16 ]合法；[5 ~ 10, 11 ~ 16 ]合法
 *			非法情况1：[1 ~ 4, 11 ~ 16];
 *			非法情况2：[1 ~ 4, 4 ~ 16];
 *			非法情况3：[1 ~ 4, 5 ~ 16, 17 ~ 8];
 *			非法情况4：[0 ~ 4, 5 ~ 10, 11 ~ 16];
 *	@2015年11月05日整理
 *
 *	@author	cntnn11
 */
function checkRewardPigaiRule($column)
{
    $c_num = 0;
    $c_res = false;
    if (is_array($column) && $column) {
        foreach ($column as $k => $row) {
            $row['snum'] = (int) $row['snum'];
            $row['enum'] = (int) $row['enum'];
            $row['money'] = $row['money'] <= 0 ? 0 : $row['money'];
            $rule_no_1 = ($row['snum'] >= $row['enum']);
            $rule_no_3 = ($row['snum'] <= 0 && $k == 0);
            if ($k <= 0 && $rule_no_1 || $rule_no_3) {
                $c_res = false;
                break;
            }

            $rule_no_2 = ($row['snum'] != $c_num);
            if ($k >= 1 && $rule_no_2) {
                $c_res = false;
                break;
            } else {
                $c_num = $row['enum'] + 1;
                $c_res = true;
            }
        }
    }

    return $c_res;
}

/**
 *  @desc	根据当前日期，获取上一周/本周/下周的起始日期和结束日期
 *			这里每周的起始日期和结束日期为： 周一零点零分零秒(00:00:00) 到 周日二十三点五十九分五十九秒(23:59:59)。
 *			周天的转换。因为欧美地区以周日为一周的开始，为符合国内意识，所以这里按照我们的规则改为7
 *
 *	@param	@string $time 某个时间戳秒数
 *	@param	时间方向，{last:上周, now:本周, next:下周}
 *
 *	@return	@array 周的起始日期和结束日期组成的数组
 */
function __getWeekDates($time = '', $direction = 'last')
{
    $week = [];
    $time = $time ? $time : time();

    $now_week_day = date('w', $time);
    $now_week_day = $now_week_day == 0 ? 7 : $now_week_day;

    // 获取本周的起始日期
    $now_week_stime = strtotime(date('Y-m-d 00:00:00', strtotime(' -'.($now_week_day - 1).'day', $time)));
    switch ($direction) {
        case 'last':
            $week['dates'] = date('Y-m-d 00:00:00', $now_week_stime - 86400 * 7);
            $week['datee'] = date('Y-m-d 23:59:59', $now_week_stime - 1);
            break;
        case 'next':
            $week['dates'] = date('Y-m-d 00:00:00', $now_week_stime + 86400 * 7);
            $week['datee'] = date('Y-m-d 23:59:59', $now_week_stime + 86400 * 13);
            break;
        case 'now':
        default:
            $week['dates'] = date('Y-m-d 00:00:00', $now_week_stime);
            $week['datee'] = date('Y-m-d 23:59:59', $now_week_stime + 86400 * 6);
            break;
    }

    return $week;
}
