<?php

namespace Sale\Handlers\PaySystem;


use Thurly\Main\Request;
use Thurly\Sale\Order;
use Thurly\Sale\PaySystem;
use Thurly\Sale\Payment;
use Thurly\Sale\PriceMaths;
use Thurly\Sale\Internals\UserBudgetPool;
use Thurly\Main\Entity\EntityError;
use Thurly\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class InnerHandler extends PaySystem\BaseServiceHandler implements PaySystem\IRefund
{
	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 * @throws \Thurly\Main\ArgumentOutOfRangeException
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$result = new PaySystem\ServiceResult();

		/** @var \Thurly\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $payment->getCollection();

		if ($paymentCollection)
		{
			/** @var \Thurly\Sale\Order $order */
			$order = $paymentCollection->getOrder();
			if ($order)
			{
				$res = $payment->setPaid('Y');
				if ($res->isSuccess())
				{
					$res = $order->save();
					if ($res)
						$result->addErrors($res->getErrors());
				}
				else
				{
					$result->addErrors($res->getErrors());
				}
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array();
	}

	/**
	 * @param Payment $payment
	 * @param int $refundableSum
	 * @return PaySystem\ServiceResult
	 */
	public function refund(Payment $payment, $refundableSum)
	{
		$result = new PaySystem\ServiceResult();

		/** @var \Thurly\Sale\PaymentCollection $paymentCollection */
		$paymentCollection = $payment->getCollection();

		/** @var \Thurly\Sale\Order $order */
		$order = $paymentCollection->getOrder();

		if ($this->isUserBudgetLock($order))
		{
			$result->addError(new EntityError(Loc::getMessage('ORDER_PSH_INNER_ERROR_USER_BUDGET_LOCK')));
			return $result;
		}

		UserBudgetPool::addPoolItem($order, $refundableSum, UserBudgetPool::BUDGET_TYPE_ORDER_UNPAY, $payment);

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	public function creditNoDemand(Payment $payment)
	{
		$result = new PaySystem\ServiceResult();

		/** @var \Thurly\Sale\PaymentCollection $collection */
		$collection = $payment->getCollection();

		/** @var \Thurly\Sale\Order $order */
		$order = $collection->getOrder();

		if ($this->isUserBudgetLock($order))
		{
			$result->addError(new EntityError(Loc::getMessage('ORDER_PSH_INNER_ERROR_USER_BUDGET_LOCK')));
			return $result;
		}

		$paymentSum = PriceMaths::roundPrecision($payment->getSum());
		$userBudget = PriceMaths::roundPrecision(UserBudgetPool::getUserBudgetByOrder($order));

		if($userBudget >= $paymentSum)
			UserBudgetPool::addPoolItem($order, ( $paymentSum * -1 ), UserBudgetPool::BUDGET_TYPE_ORDER_PAY, $payment);
		else
			$result->addError(new EntityError(Loc::getMessage('ORDER_PSH_INNER_ERROR_INSUFFICIENT_MONEY')));

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return PaySystem\ServiceResult
	 */
	public function debitNoDemand(Payment $payment)
	{
		return $this->refund($payment, $payment->getSum());
	}

	/**
	 * @param Order $order
	 * @return bool
	 */
	private function isUserBudgetLock(Order $order)
	{
		if ($userAccount = \CSaleUserAccount::GetByUserId($order->getUserId(), $order->getCurrency()))
			 return $userAccount['LOCKED'] == 'Y';

		return false;
	}
}