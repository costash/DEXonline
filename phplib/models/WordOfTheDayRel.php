<?php

class WordOfTheDayRel extends BaseObject {
  public static $_table = 'WordOfTheDayRel';

  public function countDefs($refId) {
    return Model::factory('WordOfTheDayRel')->where('redId', $refId)->count();
  }

  public static function getRefId($wotdId) {
    $wotdr = Model::factory('WordOfTheDayRel')->select('refId')->where('wotdId', $wotdId)->where_not_equal('refId', 0)->find_one();
    return $wotdr ? $wotdr->refId : null;
  }
}

?>
