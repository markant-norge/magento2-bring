<?php
namespace Markant\Bring\Api\Data;

/**
 * Shipment track search result interface.
 *
 * A shipment is a delivery package that contains products. A shipment document accompanies the shipment. This
 * document lists the products and their quantities in the delivery package. Merchants and customers can track
 * shipments.
 * @api
 */
interface ShipmentEdiSearchResultInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Gets collection items.
     *
     * @return \Markant\Bring\Api\Data\ShipmentEdiInterface[] Array of collection items.
     */
    public function getItems();

    /**
     * Set collection items.
     *
     * @param \Markant\Bring\Api\Data\ShipmentEdiInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
