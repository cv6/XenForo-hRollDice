<?php

class Hoffi_DM_FileSum
{
    public static function getHashes()
    {
        return array (
  'library\\Hoffi\\API\\Client.php' => '01e63d6a68808ad72cb099e1dac88f9e',
  'library\\Hoffi\\DM\\AdminSearchHandler\\Dice.php' => '93dcd31ebee2741a401c60150f6e908f',
  'library\\Hoffi\\DM\\AdminSearchHandler\\Rules.php' => 'aefdf60724a61f816a0a877c6bce241b',
  'library\\Hoffi\\DM\\AdminSearchHandler\\Wiresets.php' => 'fed143dc83c9528d420b5adf037882d5',
  'library\\Hoffi\\DM\\AlertHandler\\Roll.php' => '93a98d4640f5617ef291f0cd6ac7bedb',
  'library\\Hoffi\\DM\\ControllerAdmin\\Dice.php' => '0332cf3f69ef317c2ed27ef9c86809b8',
  'library\\Hoffi\\DM\\ControllerAdmin\\DiceManager\\Forum.php' => '7beb0e91268859f91702829bee1b52ff',
  'library\\Hoffi\\DM\\ControllerAdmin\\DiceManager\\Stats.php' => 'd379626c7900a4ab908b43223d6384e3',
  'library\\Hoffi\\DM\\ControllerAdmin\\DiceManager.php' => '5e4c66259e94c811d9bbf49baeca4aaf',
  'library\\Hoffi\\DM\\ControllerAdmin\\Rules.php' => '1cee8fa71cdbd1fe29a8a6f29fe4688e',
  'library\\Hoffi\\DM\\ControllerAdmin\\Wireset.php' => 'df34ff7536e4701a11a324833f272bce',
  'library\\Hoffi\\DM\\ControllerPublic\\Dice.php' => '6392c1cfd63695ce86079afdacc84f9f',
  'library\\Hoffi\\DM\\ControllerPublic\\Forum.php' => '80a528e4871193311deb7a7007d7b85c',
  'library\\Hoffi\\DM\\ControllerPublic\\Help\\Dice.php' => '32026591795c714b03a09d9dbe83671b',
  'library\\Hoffi\\DM\\ControllerPublic\\InlineMod\\Post.php' => '567fcaefbb406babba2b686093d793c3',
  'library\\Hoffi\\DM\\ControllerPublic\\Thread.php' => '4e0f14371136fa6cce4e69e02df63d8a',
  'library\\Hoffi\\DM\\Cron.php' => 'dcd9d85d27874b00d43d0c3ee9c5b48a',
  'library\\Hoffi\\DM\\DataWriter\\Dice.php' => '385644bb3fe82a8e082bdf6ecd416ffd',
  'library\\Hoffi\\DM\\DataWriter\\DiceManager\\DiscussionMessage.php' => 'af0e5ba55f2eee3225e0830a015ad251',
  'library\\Hoffi\\DM\\DataWriter\\DiceManager\\Forum.php' => 'abd706481168f9f8f7a4361a4fb0593e',
  'library\\Hoffi\\DM\\DataWriter\\DiceManager\\Thread.php' => '53ccfdff85949a50220438965884d8c2',
  'library\\Hoffi\\DM\\DataWriter\\Roll.php' => '52bd912b5f6fa34e37172607fa7fe0d2',
  'library\\Hoffi\\DM\\DataWriter\\Rules.php' => 'b6b2e552e5a96ab936911107b00ec9b5',
  'library\\Hoffi\\DM\\DataWriter\\Wireset.php' => '80acb52bcec0f512d71da90f1816951a',
  'library\\Hoffi\\DM\\Dice\\BbCode.php' => 'efcb27296bdc20db8d16a81ac345a590',
  'library\\Hoffi\\DM\\Dice\\Roller.php' => '24767b23f98c14d213f9552d423cf8bb',
  'library\\Hoffi\\DM\\Dice\\RollViewer.php' => '0595aa3ba4344397456655a0b4771e90',
  'library\\Hoffi\\DM\\Dice\\Rules.php' => '7191f2918654339b478c41fe1865425b',
  'library\\Hoffi\\DM\\Dice\\Template.php' => '186f25ac73749955fe47b026d6c31920',
  'library\\Hoffi\\DM\\EventListener\\Dice.php' => '355240b9e019ab3872ffb6f514210400',
  'library\\Hoffi\\DM\\Helpers\\Dice.php' => '6ae4d6f076eb8831c7715bcd763b9f0e',
  'library\\Hoffi\\DM\\Install.php' => '43c968f01e8eb746b98fb9395323b117',
  'library\\Hoffi\\DM\\Model\\Dice.php' => '3fb52993ba86da3963f80efd9e7647c9',
  'library\\Hoffi\\DM\\Model\\DiceManager\\Forum.php' => '2c3dfb8d13d9e53d2d38e80a7dbea929',
  'library\\Hoffi\\DM\\Model\\DiceManager\\Post.php' => '854a26671db95a0cca30d7eca3bdffa6',
  'library\\Hoffi\\DM\\Model\\DiceManager\\Stats.php' => 'c97547762e537924dec522d4517f83e5',
  'library\\Hoffi\\DM\\Model\\DiceManager\\Thread.php' => '145f70923528376cf2b95864903f7e11',
  'library\\Hoffi\\DM\\Model\\DiceManager\\User.php' => '16593b52c234c8243c7e918bd70d1a28',
  'library\\Hoffi\\DM\\Model\\Rolls.php' => '00aa0a353d03147995f3fc1d2614aa2d',
  'library\\Hoffi\\DM\\Model\\Rules.php' => '473a8d74261e5f0f87bd40aca31f7f79',
  'library\\Hoffi\\DM\\Model\\Wireset.php' => 'fd98a87d55252fefcbaeb22c30636c76',
  'library\\Hoffi\\DM\\ModeratorLogHandler\\Roll.php' => '3b1069dd7ebf8b65dd2ea301c3b04e63',
  'library\\Hoffi\\DM\\Route\\Prefix\\DiceRoll.php' => 'd1340dda368fe5bb98e4618ae622fa0e',
  'library\\Hoffi\\DM\\Route\\Prefix\\DiceThread.php' => 'c83a559e99efcceeaf6e77c107969169',
  'library\\Hoffi\\DM\\Route\\Prefix\\RollDelete.php' => 'e3ba500e67c7393384f6c35f952e092f',
  'library\\Hoffi\\DM\\Route\\PrefixAdmin\\Dice.php' => '2aeb4af9574ef7e155f02ef06549423c',
  'library\\Hoffi\\DM\\Route\\PrefixAdmin\\DiceManager.php' => '49b104a66a5e43802e367eec4c750c74',
  'library\\Hoffi\\DM\\Route\\PrefixAdmin\\Rules.php' => '134bc0111a2ad871e57872bdccb03e47',
  'library\\Hoffi\\DM\\Route\\PrefixAdmin\\Wireset.php' => '63030d3be991a6b0805ef684118502af',
  'library\\Hoffi\\DM\\StatsHandler\\Dice.php' => '812ebeb97069fa8f35b8d4e87df46a93',
  'library\\Hoffi\\DM\\ViewAdmin\\Dice.php' => '30f47f19162a36ab8b7e5b5797275928',
  'library\\Hoffi\\DM\\ViewAdmin\\Rules.php' => 'e2c229f9319332cb5d2f5045c3e1ecc5',
  'library\\Hoffi\\DM\\ViewAdmin\\Wireset.php' => '830f608a92897dff2a290d25b1dc121b',
  'library\\Hoffi\\DM\\ViewPublic\\DiceRoll.php' => '363d814ab8cc9f03fedc663e40bc37f1',
  'library\\Hoffi\\DM\\ViewPublic\\Help\\Dice.php' => '4b05d0276414e0981625c29a118f932e',
  'library\\Hoffi\\Option\\NodeChooser.php' => 'c9a338f77ef8dc1c0cca63d74664a507',
  'library\\Hoffi\\Option\\UserGroupChooser.php' => '71c0674ab25aebd2b2fa69b2fcfd78fb',
);
    }

public static function addHashes(XenForo_ControllerAdmin_Abstract $controller, array &$hashes)
    {
        $hashes += self::getHashes();
    }
}