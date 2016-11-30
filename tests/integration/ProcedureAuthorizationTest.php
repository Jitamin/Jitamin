<?php

/*
 * This file is part of Hiject.
 *
 * Copyright (C) 2016 Hiject Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once __DIR__.'/BaseProcedureTest.php';

class ProcedureAuthorizationTest extends BaseProcedureTest
{
    public function testApiCredentialDoNotHaveAccessToUserCredentialProcedure()
    {
        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->app->getMe();
    }

    public function testUserCredentialDoNotHaveAccessToAdminProcedures()
    {
        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->getUser(1);
    }

    public function testManagerCredentialDoNotHaveAccessToAdminProcedures()
    {
        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->getAllProjects();
    }

    public function testUserCredentialDoNotHaveAccessToManagerProcedures()
    {
        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->createProject('Team project creation are only for app managers');
    }

    public function testAppManagerCanCreateTeamProject()
    {
        $this->assertNotFalse($this->manager->createProject('Team project created by app manager'));
    }

    public function testAdminManagerCanCreateTeamProject()
    {
        $projectId = $this->admin->createProject('Team project created by admin');
        $this->assertNotFalse($projectId);

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->assertNotNull($this->manager->getProjectById($projectId));
    }

    public function testProjectManagerCanUpdateHisProject()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Team project can be updated',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);
        $this->assertEquals('project-manager', $this->app->getProjectUserRole($projectId, $this->managerUserId));
        $this->assertNotNull($this->manager->getProjectById($projectId));

        $this->assertTrue($this->manager->updateProject($projectId, 'My team project have been updated'));
    }

    public function testProjectAuthorizationForbidden()
    {
        $projectId = $this->manager->createProject('A team project without members');
        $this->assertNotFalse($projectId);

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->getProjectById($projectId);
    }

    public function testProjectAuthorizationGranted()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'A team project with members',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId));
        $this->assertNotNull($this->user->getProjectById($projectId));
    }

    public function testActionAuthorizationForbidden()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $actionId = $this->manager->createAction($projectId, 'task.move.column', '\Hiject\Action\TaskCloseColumn', ['column_id' => 1]);
        $this->assertNotFalse($actionId);

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->removeAction($projectId);
    }

    public function testActionAuthorizationForbiddenBecauseNotProjectManager()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $actionId = $this->manager->createAction($projectId, 'task.move.column', '\Hiject\Action\TaskCloseColumn', ['column_id' => 1]);
        $this->assertNotFalse($actionId);

        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-member'));

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->removeAction($actionId);
    }

    public function testActionAuthorizationGranted()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $actionId = $this->manager->createAction($projectId, 'task.move.column', '\Hiject\Action\TaskCloseColumn', ['column_id' => 1]);
        $this->assertNotFalse($actionId);

        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-manager'));
        $this->assertTrue($this->user->removeAction($actionId));
    }

    public function testCategoryAuthorizationForbidden()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $categoryId = $this->manager->createCategory($projectId, 'Test');
        $this->assertNotFalse($categoryId);

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->removeCategory($categoryId);
    }

    public function testCategoryAuthorizationForbiddenBecauseNotProjectManager()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $categoryId = $this->manager->createCategory($projectId, 'Test');
        $this->assertNotFalse($categoryId);

        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-member'));
        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->removeCategory($categoryId);
    }

    public function testCategoryAuthorizationGranted()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $categoryId = $this->manager->createCategory($projectId, 'Test');
        $this->assertNotFalse($categoryId);

        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-manager'));
        $this->assertTrue($this->user->removeCategory($categoryId));
    }

    public function testColumnAuthorizationForbidden()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $columnId = $this->manager->addColumn($projectId, 'Test');
        $this->assertNotFalse($columnId);

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->removeColumn($columnId);
    }

    public function testColumnAuthorizationForbiddenBecauseNotProjectManager()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $columnId = $this->manager->addColumn($projectId, 'Test');
        $this->assertNotFalse($columnId);

        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-member'));
        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->removeColumn($columnId);
    }

    public function testColumnAuthorizationGranted()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);

        $columnId = $this->manager->addColumn($projectId, 'Test');
        $this->assertNotFalse($columnId);

        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-manager'));
        $this->assertTrue($this->user->removeColumn($columnId));
    }

    public function testCommentAuthorizationForbidden()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);
        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-viewer'));

        $taskId = $this->manager->createTask('My Task', $projectId);
        $this->assertNotFalse($taskId);

        $commentId = $this->manager->createComment($taskId, $this->userUserId, 'My comment');
        $this->assertNotFalse($commentId);

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->updateComment($commentId, 'something else');
    }

    public function testCommentAuthorizationGranted()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);
        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-member'));

        $taskId = $this->user->createTask('My Task', $projectId);
        $this->assertNotFalse($taskId);

        $commentId = $this->user->createComment($taskId, $this->userUserId, 'My comment');
        $this->assertNotFalse($commentId);

        $this->assertTrue($this->user->updateComment($commentId, 'something else'));
    }

    public function testSubtaskAuthorizationForbidden()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);
        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-viewer'));

        $taskId = $this->manager->createTask('My Task', $projectId);
        $this->assertNotFalse($taskId);

        $subtaskId = $this->manager->createSubtask($taskId, 'My subtask');
        $this->assertNotFalse($subtaskId);

        $this->setExpectedException('JsonRPC\Exception\AccessDeniedException');
        $this->user->removeSubtask($subtaskId);
    }

    public function testSubtaskAuthorizationGranted()
    {
        $projectId = $this->manager->createProject([
            'name'     => 'Test Project',
            'owner_id' => $this->managerUserId,
        ]);

        $this->assertNotFalse($projectId);
        $this->assertTrue($this->manager->addProjectUser($projectId, $this->userUserId, 'project-member'));

        $taskId = $this->user->createTask('My Task', $projectId);
        $this->assertNotFalse($taskId);

        $subtaskId = $this->manager->createSubtask($taskId, 'My subtask');
        $this->assertNotFalse($subtaskId);

        $this->assertTrue($this->user->removeSubtask($subtaskId));
    }
}
