<?php

namespace ilub\plugin\SelfEvaluation\SessionHelper;

use ilub\plugin\SelfEvaluation\Block\Block;

class SessionHelper
{
    const SESSION_KEY = "xsev_data";
    const SESSION_KEY_CREATION_DATE = "creation_date";
    const SESSION_KEY_SHUFFLE = "shuffled_blocks";
    /**
     * @var string
     */
    protected $ref_id;

    function __construct($ref_id)
    {
        global $DIC;
        $this->logger = $DIC->logger()->root();

        if(!is_array($_SESSION[self::SESSION_KEY])){
            $this->logger->warning('Self Evaluation, generating new Session for all objects');
            $_SESSION[self::SESSION_KEY] = [];
        }
        $this->ref_id = $ref_id;
        if(!is_array($_SESSION[self::SESSION_KEY][$this->ref_id]))
        {
            $this->logger->warning('Self Evaluation, generating new Session for'. $this->ref_id);
            $_SESSION[self::SESSION_KEY][$this->ref_id] = [];
        }

    }

    public function resetSession()
    {
        $this->logger->warning('Self Evaluation, reset Session for'. $this->ref_id);
        $_SESSION[self::SESSION_KEY][$this->ref_id] = [];
    }

    public function initSessionCreationDate()
    {
        if (!array_key_exists(self::SESSION_KEY_CREATION_DATE, $_SESSION[self::SESSION_KEY][$this->ref_id])) {
            $this->logger->warning('Self Evaluation, init Session creation time for'. $this->ref_id);
            $_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_CREATION_DATE] = time();
        }
    }

    public function getSessionCreationDate() : int
    {
        return $_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_CREATION_DATE];
    }

    public function hasShuffledBlocks(){
        return array_key_exists(self::SESSION_KEY_SHUFFLE, $_SESSION[self::SESSION_KEY][$this->ref_id]);
    }

    /**
     * @param Block[] $blocks
     */
    public function setShuffledBlocks(array $blocks)
    {
        $_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_SHUFFLE] = serialize($blocks);
    }

    /**
     * @return Block[]
     */
    public function getShuffledeBlocks() : array{
        return unserialize($_SESSION[self::SESSION_KEY][$this->ref_id][self::SESSION_KEY_SHUFFLE]);
    }

    public function addSessionData( array $data)
    {
        $_SESSION[self::SESSION_KEY][$this->ref_id] = array_merge($_SESSION[self::SESSION_KEY][$this->ref_id], $data);
    }

    public function getSessionData() : array
    {
        return $_SESSION[self::SESSION_KEY][$this->ref_id];
    }
}
