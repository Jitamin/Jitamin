<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Hiject\Filter\TaskCommentFilter;
use Hiject\Model\CommentModel;
use Hiject\Model\ProjectModel;
use Hiject\Model\TaskFinderModel;
use Hiject\Model\TaskModel;

require_once __DIR__.'/../Base.php';

class TaskCommentFilterTest extends Base
{
    public function testMatch()
    {
        $taskFinder = new TaskFinderModel($this->container);
        $taskCreation = new TaskModel($this->container);
        $commentModel = new CommentModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $query = $taskFinder->getExtendedQuery();

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $taskCreation->create(['title' => 'Test', 'project_id' => 1]));
        $this->assertEquals(1, $commentModel->create(['task_id' => 1, 'user_id' => 1, 'comment' => 'This is a test']));

        $filter = new TaskCommentFilter();
        $filter->withQuery($query);
        $filter->withValue('test');
        $filter->apply();

        $this->assertCount(1, $query->findAll());
    }

    public function testNoMatch()
    {
        $taskFinder = new TaskFinderModel($this->container);
        $taskCreation = new TaskModel($this->container);
        $commentModel = new CommentModel($this->container);
        $projectModel = new ProjectModel($this->container);
        $query = $taskFinder->getExtendedQuery();

        $this->assertEquals(1, $projectModel->create(['name' => 'Test']));
        $this->assertEquals(1, $taskCreation->create(['title' => 'Test', 'project_id' => 1]));
        $this->assertEquals(1, $commentModel->create(['task_id' => 1, 'user_id' => 1, 'comment' => 'This is a test']));

        $filter = new TaskCommentFilter();
        $filter->withQuery($query);
        $filter->withValue('foobar');
        $filter->apply();

        $this->assertCount(0, $query->findAll());
    }
}
