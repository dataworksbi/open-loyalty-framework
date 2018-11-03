<?php
/*
 * Copyright © 2018 Divante, Inc. All rights reserved.
 * See LICENSE for license details.
 */
namespace OpenLoyalty\Bundle\TransactionBundle\Tests\Integration\Security\Voter;

use OpenLoyalty\Bundle\CoreBundle\Tests\Integration\BaseVoterTest;
use OpenLoyalty\Bundle\TransactionBundle\Security\Voter\TransactionVoter;
use OpenLoyalty\Component\Transaction\Domain\CustomerId;
use OpenLoyalty\Component\Seller\Domain\ReadModel\SellerDetailsRepository;
use OpenLoyalty\Component\Transaction\Domain\ReadModel\TransactionDetails;
use OpenLoyalty\Component\Transaction\Domain\Transaction;
use OpenLoyalty\Component\Transaction\Domain\TransactionId;
use PHPUnit_Framework_MockObject_MockObject;

/**
 * Class TransactionVoterTest.
 */
class TransactionVoterTest extends BaseVoterTest
{
    const TRANSACTION_ID = '00000000-0000-474c-b092-b0dd880c0700';
    const TRANSACTION2_ID = '00000000-0000-474c-b092-b0dd880c0701';

    /**
     * @test
     */
    public function it_works(): void
    {
        $attributes = [
            TransactionVoter::LIST_TRANSACTIONS => ['seller' => false, 'customer' => false, 'admin' => true],
            TransactionVoter::LIST_CURRENT_CUSTOMER_TRANSACTIONS => ['seller' => false, 'customer' => true, 'admin' => false],
            TransactionVoter::LIST_CURRENT_POS_TRANSACTIONS => ['seller' => true, 'customer' => false, 'admin' => false],
            TransactionVoter::VIEW => ['seller' => true, 'customer' => false, 'admin' => true, 'id' => self::TRANSACTION_ID],
            TransactionVoter::EDIT_TRANSACTION_LABELS => ['seller' => false, 'customer' => false, 'admin' => true],
            TransactionVoter::CREATE_TRANSACTION => ['seller' => false, 'customer' => false, 'admin' => true],
            TransactionVoter::ASSIGN_CUSTOMER_TO_TRANSACTION => ['seller' => true, 'customer' => true, 'admin' => true, 'subject' => $this->getTransactionMock(self::TRANSACTION_ID)],
            TransactionVoter::APPEND_LABELS_TO_TRANSACTION => ['seller' => false, 'customer' => true, 'admin' => false, 'id' => self::TRANSACTION2_ID],
            TransactionVoter::LIST_ITEM_LABELS => ['seller' => true, 'customer' => true, 'admin' => true],
        ];

        /** @var SellerDetailsRepository|\PHPUnit_Framework_MockObject_MockObject $sellerDetailsRepositoryMock */
        $sellerDetailsRepositoryMock = $this->getMockBuilder(SellerDetailsRepository::class)->getMock();
        $sellerDetailsRepositoryMock
            ->method('find')
            ->with($this->isType('string'))
            ->willReturn(null)
        ;

        $voter = new TransactionVoter($sellerDetailsRepositoryMock);

        $this->assertVoterAttributes($voter, $attributes);

        $attributes = [
            TransactionVoter::VIEW => ['seller' => true, 'customer' => true, 'admin' => true, 'id' => self::TRANSACTION2_ID],
        ];

        $this->assertVoterAttributes($voter, $attributes);
    }

    /**
     * {@inheritdoc}
     *
     * @return PHPUnit_Framework_MockObject_MockObject|TransactionDetails
     */
    protected function getSubjectById($id)
    {
        $transaction = $this->getMockBuilder(TransactionDetails::class)->disableOriginalConstructor()->getMock();
        $transaction->method('getTransactionId')->willReturn(new TransactionId($id));
        $customerId = null;
        if ($id == self::TRANSACTION2_ID) {
            $customerId = new CustomerId(self::USER_ID);
        }
        $transaction->method('getCustomerId')->willReturn($customerId);

        return $transaction;
    }

    /**
     * @param string $id
     *
     * @return PHPUnit_Framework_MockObject_MockObject
     */
    protected function getTransactionMock(string $id): PHPUnit_Framework_MockObject_MockObject
    {
        $transaction = $this->getMockBuilder(Transaction::class)->disableOriginalConstructor()->getMock();
        $transaction->method('getTransactionId')->willReturn(new TransactionId($id));
        $customerId = null;
        if ($id == self::TRANSACTION2_ID) {
            $customerId = new CustomerId(self::USER_ID);
        }
        $transaction->method('getCustomerId')->willReturn($customerId);

        return $transaction;
    }
}
