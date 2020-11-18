<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\FriendshipsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FriendshipsTable Test Case
 */
class FriendshipsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FriendshipsTable
     */
    protected $Friendships;

    /**
     * Fixtures
     *
     * @var array
     */
    protected $fixtures = [
        'app.Friendships',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Friendships') ? [] : ['className' => FriendshipsTable::class];
        $this->Friendships = $this->getTableLocator()->get('Friendships', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown(): void
    {
        unset($this->Friendships);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault(): void
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
