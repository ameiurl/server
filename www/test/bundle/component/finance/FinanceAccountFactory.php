<?php
namespace My\component\finance;

use My\context\Currency;
use My\data\company\CompanyData;
use My\data\finance\FinanceAccountData;
use My\data\member\MemberData;
use My\type\finance\FinanceAccountType;

class FinanceAccountFactory
{
    /**
     * 获取公司预收款账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getIncomeModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getIncomeData($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::INCOME;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司预收款账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取公司预付款账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getPaymentModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getPaymentData($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::PAYMENT;
            $data['origin_currency'] = $currencyCode;
            $data['recharge_amount'] = 0;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司预付款账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取公司质保金账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getGuaranteeAccountModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getGuaranteeAccountByCompanyId($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::GUARANTEE;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司质保金账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取公司冻结金额账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getFreezeAccountModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getFreezeAccountByCompanyId($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::FREEZE;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司冻结金额账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取公司授信金额账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getCreditAccountModel($companyId,$currencyCode='')
    {
        $currencyCode = $currencyCode ? $currencyCode : Currency::getInstance()->getCurrencyCode();
        $data = FinanceAccountData::getInstance()->getCreditAccountByCompanyId($companyId);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::CREDIT;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司授信金额账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取bank_id 关联的账户
     *
     * @param $bank_id
     * @return FinanceAccountModel
     */
    public static function getBankAccountModel($bank_id)
    {
        $data = FinanceAccountData::getInstance()->getDataById($bank_id);
        if(!$data){
            throw new \RuntimeException("账户不存在");
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取分销商供应商现金账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getCashModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getCashData($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::CASH;
            $data['origin_currency'] = $currencyCode;
            $data['recharge_amount'] = 0;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司现金账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }


    /**
     * 获取分销商供应商微信账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getWxModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getCashData($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::WX_PAY;
            $data['origin_currency'] = $currencyCode;
            $data['recharge_amount'] = 0;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司微信账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }


    /**
     * 获取分销商供应商支付宝账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getAltModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getCashData($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::ALI_PAY;
            $data['origin_currency'] = $currencyCode;
            $data['recharge_amount'] = 0;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司支付宝账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取分销商虚拟账户
     * @param $companyId
     * @param $currencyCode
     * @return FinanceAccountModel
     */
    public static function getWalletModel($companyId,$currencyCode)
    {
        $data = FinanceAccountData::getInstance()->getWallerData($companyId,$currencyCode);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $data = [];
            $data['company_type'] = $companyData->company_type;
            $data['company_id'] = $companyData->id;
            $data['erp_id'] = $companyData->erp_id;
            $data['account_type'] = FinanceAccountType::WALLET;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = Currency::getInstance()->getCurrencyCode();
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户或公司虚拟账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取 游客代金券账户
     * @param $memberId
     * @return FinanceAccountModel
     */
    public static function getVoucherModel($memberId)
    {
        $data = FinanceAccountData::getInstance()->getVoucherData($memberId);
        if(!$data){
            $memberData = MemberData::getInstance()->getDataById($memberId);
            if(empty($memberData)){
                throw new \RuntimeException("游客信息不正确");
            }
            $currencyCode = Currency::getInstance()->getCurrencyCode();
            $data = [];
            $data['erp_id'] = $memberData->erp_id;
            $data['company_id'] = $memberData->company_id;
            $data['company_type'] = $memberData->company_type;
            $data['member_id'] = $memberData->id;
            $data['account_type'] = FinanceAccountType::VOUCHER;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = $currencyCode;
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("游客账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 获取 游客储值账户
     * @param $memberId
     * @return FinanceAccountModel
     */
    public static function getStoredValueModel($memberId)
    {
        $data = FinanceAccountData::getInstance()->getStoredValueData($memberId);
        if(!$data){
            $memberData = MemberData::getInstance()->getDataById($memberId);
            if(empty($memberData)){
                throw new \RuntimeException("游客信息不正确");
            }
            $currencyCode = Currency::getInstance()->getCurrencyCode();
            $data = [];
            $data['erp_id'] = $memberData->erp_id;
            $data['company_id'] = $memberData->company_id;
            $data['company_type'] = $memberData->company_type;
            $data['member_id'] = $memberData->id;
            $data['account_type'] = FinanceAccountType::STORE_VALUE;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = $currencyCode;
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("游客账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }

    /**
     * 创建同业金币
     * @param $companyId
     * @return FinanceAccountModel
     */
    public static function getInnerCoinModel($companyId)
    {
        $data = FinanceAccountData::getInstance()->getInnerCoinDataByCompanyId($companyId);
        if(!$data){
            $companyData = CompanyData::getInstance()->getDataById($companyId);
            if(empty($companyData)){
                throw new \RuntimeException("客户或公司信息不正确");
            }
            $currencyCode = Currency::getInstance()->getCurrencyCode();
            $data = [];
            $data['erp_id'] = $companyData->erp_id;
            $data['company_id'] = $companyData->id;
            $data['company_type'] = $companyData->company_type;
            $data['account_type'] = FinanceAccountType::INNER_COIN;
            $data['origin_currency'] = $currencyCode;
            $data['currency'] = $currencyCode;
            $id = FinanceAccountData::getInstance()->saveInfo($data);
            if(!$id){
                throw new \RuntimeException("客户金币账户保存失败");
            }
            $data['id'] = $id;
        }
        return new FinanceAccountModel($data);
    }
}
