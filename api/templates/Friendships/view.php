<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Friendship $friendship
 */
?>
<div class="row">
    <aside class="column">
        <div class="side-nav">
            <h4 class="heading"><?= __('Actions') ?></h4>
            <?= $this->Html->link(__('Edit Friendship'), ['action' => 'edit', $friendship->id], ['class' => 'side-nav-item']) ?>
            <?= $this->Form->postLink(__('Delete Friendship'), ['action' => 'delete', $friendship->id], ['confirm' => __('Are you sure you want to delete # {0}?', $friendship->id), 'class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('List Friendships'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
            <?= $this->Html->link(__('New Friendship'), ['action' => 'add'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="friendships view content">
            <h3><?= h($friendship->id) ?></h3>
            <table>
                <tr>
                    <th><?= __('From Id') ?></th>
                    <td><?= h($friendship->from_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('To Id') ?></th>
                    <td><?= h($friendship->to_id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Id') ?></th>
                    <td><?= $this->Number->format($friendship->id) ?></td>
                </tr>
                <tr>
                    <th><?= __('Created') ?></th>
                    <td><?= h($friendship->created) ?></td>
                </tr>
            </table>
        </div>
    </div>
</div>
