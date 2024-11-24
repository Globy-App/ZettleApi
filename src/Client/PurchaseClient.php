<?php

declare(strict_types=1);

namespace LauLamanApps\IzettleApi\Client;

use LauLamanApps\IzettleApi\API\Purchase\Purchase;
use LauLamanApps\IzettleApi\API\Purchase\PurchaseHistory;
use LauLamanApps\IzettleApi\Client\Exception\NotFoundException;
use LauLamanApps\IzettleApi\Client\Purchase\Exception\PurchaseNotFoundException;
use LauLamanApps\IzettleApi\Client\Purchase\PurchaseBuilderInterface;
use LauLamanApps\IzettleApi\Client\Purchase\PurchaseHistoryBuilderInterface;
use LauLamanApps\IzettleApi\IzettleClientInterface;
use Ramsey\Uuid\UuidInterface;

final class PurchaseClient
{
    public const BASE_URL = 'https://purchase.izettle.com';

    public const GET_PURCHASE = self::BASE_URL . '/purchase/v2/%s';
    public const GET_PURCHASES = self::BASE_URL . '/purchases/v2';

    private $client;
    private $purchaseHistoryBuilder;
    private $purchaseBuilder;
	private $queryParams;
	

    public function __construct(
        IzettleClientInterface $client,
        PurchaseHistoryBuilderInterface $purchaseHistoryBuilder,
        PurchaseBuilderInterface $purchaseBuilder
    ) {
        $this->client = $client;
        $this->purchaseHistoryBuilder = $purchaseHistoryBuilder;
        $this->purchaseBuilder = $purchaseBuilder;
    }

    public function getPurchaseHistory(): PurchaseHistory
    {
        if ($this->queryParams === null) {
            $json = $this->client->getJson($this->client->get(self::GET_PURCHASES));
        } else {
            $json = $this->client->getJson($this->client->get(self::GET_PURCHASES, $this->queryParams));
        }

        return $this->purchaseHistoryBuilder->buildFromJson($json);
    }

    public function getPurchase(UuidInterface $uuid): Purchase
    {
        try {
            $response = $this->client->get(sprintf(self::GET_PURCHASE, (string) $uuid));
        } catch (NotFoundException $e) {
            throw new PurchaseNotFoundException($e->getMessage());
        }

        $json = $this->client->getJson($response);

        return $this->purchaseBuilder->buildFromJson($json);
    }
	

    /**
     * @param $key
     * @param $value
     * @return $this
     */
    public function setQueryParam($key, $value)
    {
        $this->queryParams[$key] = $value;
        return $this;
    }

    /**
     * startDate (optional)
     * The start date (inclusive) for purchases to be retrieved from until today or endDate. By default startDate is resolved to three years back.
     * @param string $startDate
     * @return $this
     */
    public function startDate(string $startDate)
    {
        return $this->setQueryParam('startDate', $startDate);
    }

    /**
     * endDate (optional)
     * The last date (exclusive) for purchases to be retrieved until.
     * @param string $endDate
     * @return $this
     */
    public function endDate(string $endDate)
    {
        return $this->setQueryParam('endDate', $endDate);
    }

    /**
     * The maximum number of records to return. Max value of limit is 1000.
     * @param int $limit
     * @return $this
     */
    public function limit(int $limit)
    {
        $limit = $limit > 999 ? 1000 : $limit;
        return $this->setQueryParam('limit',  $limit);
    }	
	
	
	/**
	 * Sort results by most recent (true)
	 *
	 * @param bool $b
	 * @return $this
	 */
	public function descending($b)
	{
		return $this->setQueryParam('descending',  $b ? 'true':'false');
	}
}
