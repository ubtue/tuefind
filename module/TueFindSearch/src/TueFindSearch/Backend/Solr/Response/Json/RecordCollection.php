<?php
namespace TueFindSearch\Backend\Solr\Response\Json;


class RecordCollection extends \VuFindSearch\Backend\Solr\Response\Json\RecordCollection {
    public function getExplainOther()
    {
        return $this->response['debug']['explainOther'] ?? [];
    }

    public function getResponseDocs()
    {
        return $this->response['response']['docs'] ?? [];
    }
}
