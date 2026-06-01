<?php
namespace TueFindSearch\Backend\Solr\Response\Json;


class RecordCollection extends \VuFindCollapseExpand\Backend\Solr\Response\Json\RecordCollection {
    public function getExplainOther()
    {
        return $this->response['debug']['explainOther'] ?? [];
    }

    public function getResponseDocs() : array
    {
        return $this->response['response']['docs'] ?? [];
    }
}