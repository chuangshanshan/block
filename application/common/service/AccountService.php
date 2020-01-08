<?php


namespace app\common\service;


use app\common\model\UserAccount;
use app\common\model\UserAccountLog;
use think\Db;

class AccountService
{
    /**
     * 用户余额变更
     * @param $userId
     * @param $changeBalance
     * @param $type
     * @param string $remark
     * @param int $businessId
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function changeUserBalance($userId, $changeBalance, $type, $remark = '', $businessId = 0)
    {
        $userAccount = UserAccount::where('user_id', $userId)->findOrFail();
        $beforeBalance = $userAccount->balance;
        $tradeNo = '';
        $amount = 0;
        $status = 2;
        $trade_time = time();
        switch ($type) {
            case 1://充值
                $tradeNo = self::generateTradeNO('CZ');
                $amount = $changeBalance;
                break;
            case 2://提现
                if ($userAccount->balance < $changeBalance) {
                    abort(401, '余额不足无法提现');
                }
                $tradeNo = self::generateTradeNO('TX');
                $amount = $changeBalance * -1;
                break;
            case 3://奖励
                $tradeNo = self::generateTradeNO('JL');
                $amount = $changeBalance;
                break;
            case 4://惩罚
                if ($userAccount->balance < $changeBalance) {
                    abort(401, '余额不足,请充值');
                }
                $tradeNo = self::generateTradeNO('CF');
                $amount = $changeBalance * -1;
                break;
            default:
                abort(401, '错误的业务类型');
        }

        if ($type != 1) {
            $userAccount->save(
                [
                    'balance' => floatval(bcadd($userAccount->balance, $amount, 6))
                ],
                [
                    'user_id' => $userId,
                    'balance' => $userAccount->balance,
                ]
            );
        }
        $userAccountLog = new UserAccountLog(
            [
                'user_id' => $userId,
                'business_id' => $businessId,
                'type' => $type,
                'trade_no' => $tradeNo,
                'amount' => $changeBalance,
                'before_balance' => $beforeBalance,
                'status' => $status,
                'remark' => $remark,
                'trade_time' => $trade_time,
            ]
        );
        $userAccountLog->save();

    }

}