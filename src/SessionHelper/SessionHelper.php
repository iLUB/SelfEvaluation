<?php

namespace ilub\plugin\SelfEvaluation\SessionHelper;

use ilub\plugin\SelfEvaluation\Block\Block;

class SessionHelper
{
    const SESSION_KEY = "xsev_data";
    const SESSION_KEY_CREATION_DATE = "creation_date";
    const SESSION_KEY_SHUFFLE = "shuffled_blocks";
    /**
     * @var array
     */
    protected $session;

    function __construct(array &$global_session, $ref_id)
    {
        if(!is_array($global_session[self::SESSION_KEY])){
            $global_session[self::SESSION_KEY] = [];
        }

        if(!is_array($global_session[self::SESSION_KEY][$ref_id]))
        {
            $global_session[self::SESSION_KEY][$ref_id] = [];
        }
        $this->session = &$global_session[self::SESSION_KEY][$ref_id];
    }

    public function resetSession()
    {

        $this->session = [];
    }

    public function initSessionCreationDate()
    {
        if (!array_key_exists(self::SESSION_KEY_CREATION_DATE, $this->session)) {
            $this->session[self::SESSION_KEY_CREATION_DATE] = time();
        }
    }

    public function getSessionCreationDate() : int
    {
        return $this->session[self::SESSION_KEY_CREATION_DATE];
    }

    public function hasShuffledBlocks(){
        return array_key_exists(self::SESSION_KEY_SHUFFLE, $this->session);
    }

    /**
     * @param Block[] $blocks
     */
    public function setShuffledBlocks(array $blocks)
    {
        $this->session[self::SESSION_KEY_SHUFFLE] = serialize($blocks);
    }

    /**
     * @return Block[]
     */
    public function getShuffledeBlocks() : array{
        return unserialize($this->session[self::SESSION_KEY_SHUFFLE]);
    }

    public function addSessionData( array $data)
    {
        $this->session = array_merge($this->session, $data);
    }

    public function getSessionData() : array
    {
        return $this->session;
    }
}
