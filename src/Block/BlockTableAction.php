<?php
namespace ilub\plugin\SelfEvaluation\Block;

class BlockTableAction
{
    /**
     * @var string
     */
    protected $title;
    /**
     * @var string
     */
    protected $cmd;
    /**
     * @var string
     */
    protected $link;
    /**
     * @var int
     */
    protected $position = 0;

    public function __construct(string $title, string $cmd, string $link, int $position = 0)
    {
        $this->title = $title;
        $this->cmd = $cmd;
        $this->link = $link;
        $this->setPosition($position);
    }

    public function getCmd() : string
    {
        return $this->cmd;
    }

    public function getLink() : string
    {
        return $this->link;
    }

    public function getTitle() : string
    {
        return $this->title;
    }

    public function setPosition(int $position)
    {
        $this->position = $position;
    }

    public function getPosition() : string
    {
        return $this->position;
    }

}