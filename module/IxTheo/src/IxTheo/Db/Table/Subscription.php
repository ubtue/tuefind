<?php
namespace IxTheo\Db\Table;
use VuFind\Db\Row\RowGateway;
use VuFind\Db\Table\PluginManager;
use Laminas\Db\Adapter\Adapter;

class Subscription extends \VuFind\Db\Table\Gateway implements \VuFind\Db\Table\DbTableAwareInterface
{
    use \VuFind\Db\Table\DbTableAwareTrait;

    /**
     * Session container for last list information.
     *
     * @var \Laminas\Session\Container
     */
    protected $session;

    /**
     * Constructor
     *
     * @param Adapter       $adapter Database adapter
     * @param PluginManager $tm      Table manager
     * @param array         $cfg     Laminas Framework configuration
     * @param RowGateway    $rowObj  Row prototype object (null for default)
     * @param string        $table   Name of database table to interface with
     */
    public function __construct(Adapter $adapter, PluginManager $tm, $cfg,
        RowGateway $rowObj = null, $table = 'ixtheo_journal_subscriptions'
    ) {
        parent::__construct($adapter, $tm, $cfg, $rowObj, $table);
    }

    public function getNew($userId, $recordId) {
        $row = $this->createRow();
        $row->user_id = $userId;
        $row->journal_control_number_or_bundle_name = $recordId;
        $row->max_last_modification_time = date('Y-m-d 00:00:00');
        return $row;
    }

    public function findExisting($userId, $recordId) {
        return $this->select(['user_id' => $userId, 'journal_control_number_or_bundle_name' => $recordId])->current();
    }

    public function subscribe($userId, $recordId) {
        $row = $this->getNew($userId, $recordId);
        $row->save();
        return $row->user_id;
    }

    public function unsubscribe($userId, $recordId) {
        return $this->delete(['user_id' => $userId, 'journal_control_number_or_bundle_name' => $recordId]);
    }

    public function getAll($userId, $sort) {
        $select = $this->getSql()->select()->where(['user_id' => $userId]);
        $this->applySort($select, $sort);
        return $this->selectWith($select);
    }

    public function get($userId, $sort, $start, $limit) {
        $select = $this->getSql()->select()->where(['user_id' => $userId])->offset($start)->limit($limit);
        $this->applySort($select, $sort);
        return $this->selectWith($select);
    }

    /**
     * Apply a sort parameter to a query on the resource table.
     *
     * @param \Laminas\Db\Sql\Select $query Query to modify
     * @param string                 $sort  Field to use for sorting (may include 'desc'
     * qualifier)
     *
     * @return void
     */
    public static function applySort($query, $sort)
    {
        // Apply sorting, if necessary:
        $legalSorts = [
            // deprecated, sorting is done on php side
            // (fields like "title" are no longer stored in mysql,
            // else we have updating problem e.g. if title is changed in original data)
            'journal_title'
        ];
        if (!empty($sort) && in_array(strtolower($sort), $legalSorts)) {
            $query->order([$sort]);
        }
    }
}
