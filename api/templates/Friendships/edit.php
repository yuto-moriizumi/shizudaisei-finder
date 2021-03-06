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
            <?= $this->Form->postLink(
                __('Delete'),
                ['action' => 'delete', $friendship->id],
                ['confirm' => __('Are you sure you want to delete # {0}?', $friendship->id), 'class' => 'side-nav-item']
            ) ?>
            <?= $this->Html->link(__('List Friendships'), ['action' => 'index'], ['class' => 'side-nav-item']) ?>
        </div>
    </aside>
    <div class="column-responsive column-80">
        <div class="friendships form content">
            <?= $this->Form->create($friendship) ?>
            <fieldset>
                <legend><?= __('Edit Friendship') ?></legend>
                <?php
                    echo $this->Form->control('from_id');
                    echo $this->Form->control('to_id');
                ?>
            </fieldset>
            <?= $this->Form->button(__('Submit')) ?>
            <?= $this->Form->end() ?>
        </div>
    </div>
</div>
