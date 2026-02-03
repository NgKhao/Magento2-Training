<?php
namespace Magenest\Movie\Model\ResourceModel\Blog;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    protected function _construct()
    {
        $this->_init(\Magenest\Movie\Model\Blog::class, \Magenest\Movie\Model\ResourceModel\Blog::class);
    }

    /**
     * Join with admin_user to get author info
     *
     * @return $this
     */
    public function joinAuthor()
    {
        $this->getSelect()->joinLeft(
            ['admin_user' => $this->getTable('admin_user')],
            'main_table.author_id = admin_user.user_id',
            ['author_username' => 'username', 'author_firstname' => 'firstname', 'author_lastname' => 'lastname', 'author_email' => 'email']
        );
        return $this;
    }
}
