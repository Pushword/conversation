<?php

namespace Pushword\conversation\Tests\Admin;

use Pushword\Admin\Tests\AbstractAdminTestClass;

class ConversationAdminTest extends AbstractAdminTestClass
{
    public function testAdmin()
    {
        $client = $this->loginUser();

        $client->catchExceptions(false);

        $actions = ['list', 'create'];

        foreach ($actions as $action) {
            $client->request('GET', '/admin/pushword/conversation/message/'.$action);
            $this->assertResponseIsSuccessful();
        }
    }
}
