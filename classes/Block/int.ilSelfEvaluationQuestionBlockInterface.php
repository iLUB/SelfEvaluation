<?php

/**
 * interface ilSelfEvaluationQuestionBlockInterface
 *
 * @author       Timon Amstutz <timon.amstutz@ilub.unibe.ch>
 *
 * @version 2.0.6
 */
interface ilSelfEvaluationQuestionBlockInterface {
    /**
     * @return int
     */
    public function getId();
    /**
     * @return string
     */
    public function getDescription();

    /**
     * @return int
     */
    public function getParentId();

    /**
     * @return int
     */
    public function getPosition();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @return string
     */
    public function getAbbreviation();
    /**
     * @return ilSelfEvaluationQuestion[]
     */
    public function getQuestions();
}

?>
