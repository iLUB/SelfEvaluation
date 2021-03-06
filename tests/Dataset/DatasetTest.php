<?php
require_once __DIR__ . '/../../vendor/autoload.php';

include_once "DatasetHelperTrait.php";

use PHPUnit\Framework\TestCase;
use \ilub\plugin\SelfEvaluation\Dataset\Dataset;
use ilub\plugin\SelfEvaluation\Dataset\Data;
use \ilub\plugin\SelfEvaluation\Block\Matrix\QuestionBlock;
use \ilub\plugin\SelfEvaluation\Question\Matrix\Question;

class DatasetTest extends TestCase
{
    use DatasetHelperTrait;
    /**
     * @var Dataset
     */
    protected $dataset;

    /**
     * @var  \Mockery\MockInterface|\ilDBInterface
     */
    protected $db;

    public function setUp():void
    {
        $this->db = \Mockery::mock("\ilDBInterface");
        $this->dataset = new Dataset($this->db);
    }

    public function testConstruct()
    {
        self::assertEquals(Dataset::class, get_class($this->dataset));
    }

    public function testIdAfterConstruct()
    {
        self::assertEquals(0, $this->dataset->getId());
    }

    public function testSetId()
    {
        $this->dataset->setId(1);
        self::assertEquals(1, $this->dataset->getId());
    }

    public function testGetArrayForDBOnEmpty()
    {
        self::assertEquals(['id' => ['integer', 0],
                            'identifier_id' => ['integer', 0],
                            'creation_date' => ['integer', 0]
        ], $this->dataset->getArrayForDb());
    }

    public function testUpdateValuesByEmptyPost()
    {
        $data = [];
        $this->db->shouldReceive("nextId")->with($this->dataset::TABLE_NAME)->andReturn(1);
        $this->dataset->setId(1);
        $this->db->shouldReceive("insert")->with([$this->dataset::TABLE_NAME, $this->dataset->getArrayForDb()]);
        $this->dataset->updateValuesByPost($data);
        self::assertTrue(true);
    }

    public function testUpdateValuesByPostQuestion()
    {
        $data = ["qst_1" => "value1"];
        $data_fixture = function ($argument) {
            return $argument['id'] == [0 => 'integer', 1 => 2] &&
                $argument['dataset_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_type'] == [0 => 'text', 1 => 'qst'] &&
                $argument['creation_date'][0] == 'integer' &&
                $argument['value'] == [0 => 'text', 1 => 'value1'];
        };

        $this->db->shouldReceive("nextId")->with($this->dataset::TABLE_NAME)->andReturn(1);
        $this->dataset->setId(1);
        $this->db->shouldReceive("insert")->with([$this->dataset::TABLE_NAME, $this->dataset->getArrayForDb()]);
        $this->db->shouldReceive("nextId")->with(Data::TABLE_NAME)->andReturn(2);
        $this->db->shouldReceive("prepare");
        $this->db->shouldReceive("execute");
        $this->db->shouldReceive("fetchObject");

        $this->checkMockeryForUpdateValuesByPost($data, [$data_fixture]);

        $this->dataset->updateValuesByPost($data);

        self::assertTrue(true);
    }

    public function testUpdateValuesByPostMetaQuestion()
    {
        $data = ["mqst_1" => "value1"];
        $data_fixture = function ($argument) {
            return $argument['id'] == [0 => 'integer', 1 => 2] &&
                $argument['dataset_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_type'] == [0 => 'text', 1 => 'mqst'] &&
                $argument['creation_date'][0] == 'integer' &&
                $argument['value'] == [0 => 'text', 1 => 'value1'];
        };

        $this->checkMockeryForUpdateValuesByPost($data, [$data_fixture]);

        self::assertTrue(true);
    }

    public function testUpdateValuesByPostMetaQuestionCombination()
    {
        $data = ["qst_1" => "value1", "qst_2" => "value2", "mqst_1" => "value1"];
        $data_fixture1 = function ($argument) {
            return $argument['id'] == [0 => 'integer', 1 => 2] &&
                $argument['dataset_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_type'] == [0 => 'text', 1 => 'qst'] &&
                $argument['creation_date'][0] == 'integer' &&
                $argument['value'] == [0 => 'text', 1 => 'value1'];
        };
        $data_fixture2 = function ($argument) {
            return $argument['id'] == [0 => 'integer', 1 => 2] &&
                $argument['dataset_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_type'] == [0 => 'text', 1 => 'mqst'] &&
                $argument['creation_date'][0] == 'integer' &&
                $argument['value'] == [0 => 'text', 1 => 'value1'];
        };
        $data_fixture3 = function ($argument) {
            return $argument['id'] == [0 => 'integer', 1 => 2] &&
                $argument['dataset_id'] == [0 => 'integer', 1 => 1] &&
                $argument['question_id'] == [0 => 'integer', 1 => 2] &&
                $argument['question_type'] == [0 => 'text', 1 => 'qst'] &&
                $argument['creation_date'][0] == 'integer' &&
                $argument['value'] == [0 => 'text', 1 => 'value2'];
        };

        $this->checkMockeryForUpdateValuesByPost($data, [$data_fixture1, $data_fixture2, $data_fixture3]);

        self::assertTrue(true);
    }

    protected function checkMockeryForUpdateValuesByPost($data, $fixtures)
    {
        $this->db->shouldReceive("nextId")->with($this->dataset::TABLE_NAME)->andReturn(1);
        $this->dataset->setId(1);
        $this->db->shouldReceive("insert")->with([$this->dataset::TABLE_NAME, $this->dataset->getArrayForDb()]);
        $this->db->shouldReceive("nextId")->with(Data::TABLE_NAME)->andReturn(2);
        $this->db->shouldReceive("prepare");
        $this->db->shouldReceive("execute");
        $this->db->shouldReceive("fetchObject");

        foreach ($fixtures as $fixture) {
            $this->db->shouldReceive("insert")->with(Data::TABLE_NAME, Mockery::on($fixture));

        }

        $this->dataset->updateValuesByPost($data);
    }

    public function testSetHighestScale()
    {
        $this->dataset->setHighestScale(75);
        self::assertEquals(75, $this->dataset->getHighestValueFromScale());
    }

    public function testSetQuestionBlocksEmpty()
    {
        $this->dataset->setQuestionBlocks([]);
        self::assertEquals([], $this->dataset->getQuestionBlocks());
    }

    public function testSetQuestionBlocksNotEmpty()
    {
        $block1 = new QuestionBlock($this->db);
        $block1->setId(1);
        $this->dataset->setQuestionBlocks([$block1]);
        self::assertEquals([$block1], $this->dataset->getQuestionBlocks());
    }

    public function testSetQuestionsDataForBlocksEmpty()
    {
        $block1 = new QuestionBlock($this->db);
        $block1->setId(1);
        $this->dataset->setQuestionsDataForBlocks([$block1->getId() => []]);
        self::assertEquals([], $this->dataset->getQuestionsDataPerBlock($block1->getId()));
    }

    public function testSetQuestionsDataForBlocksNotEmpty()
    {
        $block1 = new QuestionBlock($this->db);
        $block1->setId(1);
        $question1 = new Question($this->db);
        $question1->setId(1);
        $answer = new Data($this->db);
        $answer->setValue(0);
        $this->dataset->setQuestionsDataForBlocks([$block1->getId() => [$question1->getId() => $answer]]);
        self::assertEquals([$question1->getId() => $answer],
            $this->dataset->getQuestionsDataPerBlock($block1->getId()));
    }

    public function testGetPercentageForBlockOnEmptyDataSet()
    {
        $this->dataset->setHighestScale(75);
        $this->dataset->setQuestionBlocks([]);
        self::assertNull($this->dataset->getPercentageForBlock(1));
    }

    public function testGetPercentageForBlockOnSingularSetNoAnswer()
    {
        $this->dataset->setHighestScale(75);
        $block1 = $this->getBasicBlock();
        $this->dataset->setQuestionBlocks([$block1]);
        $this->dataset->setQuestionsDataForBlocks([$block1->getId() => []]);
        try {
            $this->dataset->getPercentageForBlock(1);
            self::assertTrue(false);
        } catch (Exception $e) {
            self::assertTrue(true);
        }
    }

    public function testGetPercentageForBlockOnSingularSetZeroAnswer()
    {
        $this->dataset->setHighestScale(5);
        [$block1, $question1, $answer1] = $this->getBasics();
        $answer1->setValue(0);
        $this->dataset->setQuestionBlocks([$block1]);
        $this->dataset->setQuestionsDataForBlocks([$block1->getId() => [$question1->getId() => $answer1]]);

        self::assertEquals(0 / 5 * 100, $this->dataset->getPercentageForBlock(1));
    }

    public function testGetPercentageForBlockOnSingularSetMaxAnswer()
    {
        $this->dataset->setHighestScale(5);
        [$block1, $question1, $answer1] = $this->getBasics();
        $answer1->setValue(5);
        $this->dataset->setQuestionBlocks([$block1]);
        $this->dataset->setQuestionsDataForBlocks([$block1->getId() => [$question1->getId() => $answer1]]);

        self::assertEquals(5 / 5 * 100, $this->dataset->getPercentageForBlock(1));
    }

    public function testGetPercentageForBlockOnSingularSetMediumAnswer()
    {
        $this->dataset->setHighestScale(5);
        [$block1, $question1, $answer1] = $this->getBasics();
        $answer1->setValue(2);
        $this->dataset->setQuestionBlocks([$block1]);
        $this->dataset->setQuestionsDataForBlocks([$block1->getId() => [$question1->getId() => $answer1]]);

        self::assertEquals(2 / 5 * 100, $this->dataset->getPercentageForBlock(1));
    }

    public function testGetPercentageForBlockOnSingularSetReversedScale()
    {
        $this->dataset->setHighestScale(5);
        [$block1, $question1, $answer1] = $this->getBasics();
        $this->dataset->setQuestionBlocks([$block1]);
        $answer1->setValue(2);
        $this->dataset->setQuestionsDataForBlocks([$block1->getId() => [$question1->getId() => $answer1]]);

        self::assertEquals(2 / 5 * 100, $this->dataset->getPercentageForBlock(1));
    }

    public function testGetPercentageForBlockWithMultipleQuestionAnswers()
    {
        $this->dataset = $this->setUpDatasetWithThreeBlocks($this->dataset);
        $percentages = $this->getBlockThreeBlocksPercentages();
        self::assertEquals($percentages[$this->getBlock1()->getId()], $this->dataset->getPercentageForBlock($this->getBlock1()->getId()));
    }

    public function testGetPercentagePerBlockWithMultipleQuestionAnswers()
    {
        $this->dataset = $this->setUpDatasetWithThreeBlocks($this->dataset);
        self::assertEquals($this->getBlockThreeBlocksPercentages(), $this->dataset->getPercentagePerBlock());
    }

    public function testGetMinPercentageBlock()
    {
        $this->dataset = $this->setUpDatasetWithThreeBlocks($this->dataset);
        self::assertEquals([$this->getBlock2(), $this->getBlock2Percentage()], $this->dataset->getMinPercentageBlockAndMin());
    }

    public function testGetMaxPercentageBlock()
    {
        $this->dataset = $this->setUpDatasetWithThreeBlocks($this->dataset);
        self::assertEquals([$this->getBlock1(), $this->getBlock1Percentage()], $this->dataset->getMaxPercentageBlockAndMax());
    }
}



