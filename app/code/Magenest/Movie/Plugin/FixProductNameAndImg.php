<?php
namespace Magenest\Movie\Plugin;

use Magento\Quote\Model\Quote\Item as QuoteItem;
use Magento\Checkout\CustomerData\ItemPool;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Helper\Image;




class FixProductNameAndImg
{
    public function __construct(ProductRepositoryInterface $productRepository, Image $image)
    {
        $this->productRepository = $productRepository;
        $this->image = $image;
    }

    public function  afterGetItemData(ItemPool $subject,array $result, QuoteItem $item)
    {
        if($item->getProductType() !== Configurable::TYPE_CODE){
            return $result;
        }

        $children = $this->getSelectedChildProduct($item);

        if(!$children)
            return $result;

        $result['product_name'] = (string)$children->getName();

        $image = $this->image->init($children, 'mini_cart_product_thumbnail');

        if(isset($result['product_image']) && is_array($result['product_image'])){
            $result['product_image']['src'] = $image->getUrl();
        }

        return $result;
    }

    public function  getSelectedChildProduct(QuoteItem $item)
    {
        $children = $item->getChildren();
        if(is_array($children) && !empty($children)){
            $childrenItem = $children[0];
            $p = $childrenItem->getProduct();
            if($p instanceof Product && $p->getId())
            {
                return $p;
            }
        }
        return null;

    }

}
