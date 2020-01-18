<?php
include_once 'config.php';
include_once 'functions/recGlob.php';
include_once 'functions/getColorForClass.php';
include_once 'functions/filterBackups.php';

$files = recGlob($path);
$files = filterBackups($files);

$items = [];
$file = fopen('items.csv', 'r');
while (($line = fgetcsv($file)) !== FALSE) {
    $items[$line[0]] = $line;
}
fclose($file);

$GLOBALS['items'] = $items;

// here to have item array rdy
include_once 'functions/getBadgeForClass.php';
?>

<html lang="de">
<head>
    <title>TEVE Loader</title>
    <style>
        table {
            border-collapse: collapse;
        }

        table, th, td {
            border: 1px solid black;
        }

        .green {
            background-color: green;
            cursor: pointer;
        }

        .red {
            background-color: red;
            cursor: not-allowed;
        }

        .reset-cursor {
            cursor: default !important;
        }

        .mytooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black;
        }

        .mytooltiptext {
            width: 300px !important;
            text-align: left !important;
            padding-left: 5px !important;
        }

        .mytooltip .mytooltiptext {
            visibility: hidden;
            width: 120px;
            background-color: #555;
            color: #fff;
            text-align: center;
            border-radius: 6px;
            padding: 5px 0;
            position: absolute;
            z-index: 1;
            bottom: 125%;
            left: 50%;
            margin-left: -60px;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .mytooltip .mytooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 20%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .mytooltip:hover .mytooltiptext {
            visibility: visible;
            opacity: 1;
        }

        .mytooltiptext {
            font-size: 15px;
        }

        .selected {
            background: gold !important;
        }

        .classes td {
            vertical-align: top;
        }

        .board td {
            font-size: 12px;
        }
    </style>
    <script type="text/javascript" src="assets/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="assets/bootstrap.min.js"></script>
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <link rel="stylesheet" href="assets/bootstrap-grid.min.css">
    <link rel="stylesheet" href="assets/bootstrap-reboot.min.css">
</head>
<body style="background-color: #373737; overflow-x: hidden;">
<?php
$data = [];
foreach ($files as $file) {
    $version = '';
    $wcversion = '';
    $hero = '';
    $level = '';
    $player = '';
    $gold = '';
    $shards = '';
    $heroItems = false;
    $stashItems = false;
    $heroItemsArray = [];
    $stashItemsArray = [];
    $itemCode = false;
    $itemCodeString = '';
    $heroCode = false;
    $heroCodeString = '';
    $txt = file_get_contents($file);
    $lines = explode("\r\n\t", $txt);
    foreach ($lines as $line) {
        if (strpos($line, 'call Preload( "') === 0) {
            //get important parts
            $line = str_replace('call Preload( "', '', $line);
            $line = str_replace('" )', '', $line);
            if ($line === "MAP: Twilight's Eve Final Fixxed" ||
                $line === "Brought to you by Donach#6231 and KryptonRazer#3935, developers of Teve Reborn!" ||
                $line === "Join us at discord.gg/vyhPqgA and visit tever.xyz") {
                continue;
            }
            if (strpos($line, 'Version: ') === 0) {
                $version = str_replace('Version: ', '', $line);
            }
            if (strpos($line, 'Recommended Wc3 Version: ') === 0) {
                $wcversion = str_replace('Recommended Wc3 Version: ', '', $line);
            }
            if (strpos($line, 'Player Name: ') === 0) {
                $player = str_replace('Player Name: ', '', $line);
            }
            if (strpos($line, 'Hero: ') === 0) {
                $t = explode('[', str_replace(['Hero: ', ']'], '', $line));
                $hero = trim($t[0]);
                $level = (int)str_replace('Lv.', '', $t[1]);
            }
            if (strpos($line, 'Gold: ') === 0) {
                $t = explode('  ', $line);
                $gold = (int)str_replace('Gold: ', '', $t[0]);
                $shards = (int)str_replace('Shards: ', '', $t[1]);
            }
            if (strpos($line, 'Load Type: Hero') === 0) {
                $heroCode = true;
                continue;
            }
            if ($heroCode) {
                $heroCodeString = $line;
                $heroCode = false;
                continue;
            }
            if (strpos($line, 'Load Type: Item') === 0) {
                $itemCode = true;
                continue;
            }
            if ($itemCode) {
                $itemCodeString = $line;
                $itemCode = false;
            }
            if (strpos($line, 'Items Worn by Hero: ') === 0) {
                $heroItems = true;
                continue;
            }
            if (strpos($line, 'Items in Stash: ') === 0) {
                $heroItems = false;
                $stashItems = true;
                continue;
            }
            if (strpos($line, '        ') === 0) {
                $stashItems = false;
                continue;
            }
            if ($heroItems) {
                $heroItemsArray[] = $line;
            }
            if ($stashItems) {
                $stashItemsArray[] = $line;
            }
        }
    }
    while (count($heroItemsArray) !== 6) {
        $heroItemsArray[] = '';
    }
    while (count($stashItemsArray) !== 6) {
        $stashItemsArray[] = '';
    }
    $data[$hero][] = array(
        'v' => $version,
        'wcv' => $wcversion,
        'p' => $player,
        'lvl' => $level,
        'g' => $gold,
        's' => $shards,
        'hero' => $heroItemsArray,
        'stash' => $stashItemsArray,
        'load' => $heroCodeString,
        'load2' => $itemCodeString
    );
}
foreach ($data as $k => $d) {
    usort($d, function ($a, $b) {
        if ($a['lvl'] === $b ['lvl']) return 0;
        return ($a['lvl'] < $b['lvl']) ? -1 : 1;
    });
}
?>
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <table class="classes w-100">
                <tr>
                    <td colspan="11" class="<?= getColorForClass($data, 'Novice (Male)') ?>">Novice
                        (Male)<?= getBadgeForClass($data, 'Novice (Male)') ?></td>
                    <td colspan="7" class="<?= getColorForClass($data, 'Novice (Female)') ?>">Novice
                        (Female)<?= getBadgeForClass($data, 'Novice (Female)') ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="<?= getColorForClass($data, 'Swordsman') ?>">
                        Swordsman<?= getBadgeForClass($data, 'Swordsman') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Initiate') ?>">
                        Initiate<?= getBadgeForClass($data, 'Initiate') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Thief') ?>">
                        Thief<?= getBadgeForClass($data, 'Thief') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Witch Hunter') ?>">Witch
                        Hunter<?= getBadgeForClass($data, 'Witch Hunter') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Druid') ?>">
                        Druid<?= getBadgeForClass($data, 'Druid') ?></td>
                    <td class="<?= getColorForClass($data, 'Acolyte (Male)') ?>">Acolyte
                        (Male)<?= getBadgeForClass($data, 'Acolyte (Male)') ?></td>
                    <td class="<?= getColorForClass($data, 'Acolyte (Female)') ?>">Acolyte
                        (Female)<?= getBadgeForClass($data, 'Acolyte (Female)') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Archer') ?>">
                        Archer<?= getBadgeForClass($data, 'Archer') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Templar') ?>">
                        Templar<?= getBadgeForClass($data, 'Templar') ?></td>
                </tr>
                <tr>
                    <td colspan="2" class="<?= getColorForClass($data, 'Knight') ?>">
                        Knight<?= getBadgeForClass($data, 'Knight') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Mage') ?>">
                        Mage<?= getBadgeForClass($data, 'Mage') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Rogue') ?>">
                        Rogue<?= getBadgeForClass($data, 'Rogue') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Slayer') ?>">
                        Slayer<?= getBadgeForClass($data, 'Slayer') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'ArchDruid') ?>">
                        ArchDruid<?= getBadgeForClass($data, 'ArchDruid') ?></td>
                    <td class="<?= getColorForClass($data, 'Cleric (Male)') ?>">Cleric
                        (Male)<?= getBadgeForClass($data, 'Cleric (Male)') ?></td>
                    <td class="<?= getColorForClass($data, 'Cleric (Female)') ?>">Cleric
                        (Female)<?= getBadgeForClass($data, 'Cleric (Female)') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'Hunter') ?>">
                        Hunter<?= getBadgeForClass($data, 'Hunter') ?></td>
                    <td colspan="2" class="<?= getColorForClass($data, 'ArchTemplar') ?>">
                        ArchTemplar<?= getBadgeForClass($data, 'ArchTemplar') ?></td>
                </tr>
                <tr>
                    <td class="<?= getColorForClass($data, 'Crusader') ?>">
                        Crusader<?= getBadgeForClass($data, 'Crusader') ?></td>
                    <td class="<?= getColorForClass($data, 'Imperial Knight') ?>">Imperial
                        Knight<?= getBadgeForClass($data, 'Imperial Knight') ?></td>
                    <td class="<?= getColorForClass($data, 'Wizard') ?>">
                        Wizard<?= getBadgeForClass($data, 'Wizard') ?></td>
                    <td class="<?= getColorForClass($data, 'Sage') ?>">Sage<?= getBadgeForClass($data, 'Sage') ?></td>
                    <td class="<?= getColorForClass($data, 'Assassin') ?>">
                        Assassin<?= getBadgeForClass($data, 'Assassin') ?></td>
                    <td class="<?= getColorForClass($data, 'Stalker') ?>">
                        Stalker<?= getBadgeForClass($data, 'Stalker') ?></td>
                    <td class="<?= getColorForClass($data, 'Witcher') ?>">
                        Witcher<?= getBadgeForClass($data, 'Witcher') ?></td>
                    <td class="<?= getColorForClass($data, 'Inquisitor') ?>">
                        Inquisitor<?= getBadgeForClass($data, 'Inquisitor') ?></td>
                    <td class="<?= getColorForClass($data, 'Shaman') ?>">
                        Shaman<?= getBadgeForClass($data, 'Shaman') ?></td>
                    <td class="<?= getColorForClass($data, 'Shapeshifter') ?>">
                        Shapeshifter<?= getBadgeForClass($data, 'Shapeshifter') ?></td>
                    <td class="<?= getColorForClass($data, 'Priest') ?>">
                        Priest<?= getBadgeForClass($data, 'Priest') ?></td>
                    <td class="<?= getColorForClass($data, 'Matriarch') ?>">
                        Matriarch<?= getBadgeForClass($data, 'Matriarch') ?></td>
                    <td class="<?= getColorForClass($data, 'Marksman') ?>">
                        Marksman<?= getBadgeForClass($data, 'Marksman') ?></td>
                    <td class="<?= getColorForClass($data, 'Tracker') ?>">
                        Tracker<?= getBadgeForClass($data, 'Tracker') ?></td>
                    <td class="<?= getColorForClass($data, 'High Templar') ?>">High
                        Templar<?= getBadgeForClass($data, 'High Templar') ?></td>
                    <td class="<?= getColorForClass($data, 'Dark Templar') ?>">Dark
                        Templar<?= getBadgeForClass($data, 'Dark Templar') ?></td>
                </tr>
                <tr>
                    <td class="<?= getColorForClass($data, 'Avenger') ?>">
                        Avenger<?= getBadgeForClass($data, 'Avenger') ?></td>
                    <td class="<?= getColorForClass($data, 'Champion') ?>">
                        Champion<?= getBadgeForClass($data, 'Champion') ?></td>
                    <td class="<?= getColorForClass($data, 'White Wizard') ?>">White
                        Wizard<?= getBadgeForClass($data, 'White Wizard') ?></td>
                    <td class="<?= getColorForClass($data, 'ArchSage') ?>">
                        ArchSage<?= getBadgeForClass($data, 'ArchSage') ?></td>
                    <td class="<?= getColorForClass($data, 'Phantom Assassin') ?>">Phantom
                        Assassin<?= getBadgeForClass($data, 'Phantom Assassin') ?></td>
                    <td class="<?= getColorForClass($data, 'Master Stalker') ?>">Master
                        Stalker<?= getBadgeForClass($data, 'Master Stalker') ?></td>
                    <td class="<?= getColorForClass($data, 'Professional Witcher') ?>">Professional
                        Witcher<?= getBadgeForClass($data, 'Professional Witcher') ?></td>
                    <td class="<?= getColorForClass($data, 'Grand Inquisitor') ?>">Grand
                        Inquisitor<?= getBadgeForClass($data, 'Grand Inquisitor') ?></td>
                    <td class="<?= getColorForClass($data, 'Summoner') ?>">
                        Summoner<?= getBadgeForClass($data, 'Summoner') ?></td>
                    <td class="<?= getColorForClass($data, 'RuneMaster') ?>">
                        RuneMaster<?= getBadgeForClass($data, 'RuneMaster') ?></td>
                    <td class="<?= getColorForClass($data, 'Hierophant') ?>">
                        Hierophant<?= getBadgeForClass($data, 'Hierophant') ?></td>
                    <td class="<?= getColorForClass($data, 'Prophetess') ?>">
                        Prophetess<?= getBadgeForClass($data, 'Prophetess') ?></td>
                    <td class="<?= getColorForClass($data, 'Sniper') ?>">
                        Sniper<?= getBadgeForClass($data, 'Sniper') ?></td>
                    <td class="<?= getColorForClass($data, 'Monster Hunter') ?>">Monster
                        Hunter<?= getBadgeForClass($data, 'Monster Hunter') ?></td>
                    <td class="<?= getColorForClass($data, 'Grand Templar') ?>">Grand
                        Templar<?= getBadgeForClass($data, 'Grand Templar') ?></td>
                    <td class="<?= getColorForClass($data, 'Dark ArchTemplar') ?>">Dark
                        ArchTemplar<?= getBadgeForClass($data, 'Dark ArchTemplar') ?></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <?php
            foreach ($data as $class => $info):?>
                <div id="<?= str_replace([' ', '(', ')'], ['_', '', ''], $class) ?>" class="toBeHide"
                     style="display: none;">
                    <div style="color: white;font-size: 25px;"><?= $class ?></div>
                    <table style="color: white" class="board w-100">
                        <tr>
                            <th>Level</th>
                            <th>Gold</th>
                            <th>Shards</th>
                            <th>Item #1</th>
                            <th>Item #2</th>
                            <th>Item #3</th>
                            <th>Item #4</th>
                            <th>Item #5</th>
                            <th>Item #6</th>
                            <th>Stash #1</th>
                            <th>Stash #2</th>
                            <th>Stash #3</th>
                            <th>Stash #4</th>
                            <th>Stash #5</th>
                            <th>Stash #6</th>
                            <th>load</th>
                            <th>load2</th>
                        </tr>
                        <?php foreach ($info as $c): ?>
                            <tr>
                                <td class="lvl"><?= $c['lvl']; ?></td>
                                <td class="gold"><?= number_format($c['g']); ?></td>
                                <td class="shards"><?= $c['s']; ?></td>
                                <?php foreach ($c['hero'] as $i): ?>
                                    <td>
                                        <div class="mytooltip"><?= $i ?>
                                            <?php $i = preg_replace('/ \(.*\)/', '', $i); ?>
                                            <span class="mytooltiptext"><?= $items[$i][0]; ?><br><?= $items[$i][1] ?><br><?= str_replace('.', "<br>", $items[$i][2]); ?></span>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                                <?php foreach ($c['stash'] as $i): ?>
                                    <td>
                                        <div class="mytooltip"><?= $i ?>
                                            <?php $i = preg_replace('/ \(.*\)/', '', $i); ?>
                                            <span class="mytooltiptext"><?= $items[$i][0]; ?><br><?= $items[$i][1] ?><br><?= str_replace('.', "<br>", $items[$i][2]); ?></span>
                                        </div>
                                    </td>
                                <?php endforeach; ?>
                                <td style="width: 40px;"><input name="load" type="text" value="<?= $c['load'] ?>"
                                                                style="width: 40px;!important"
                                                                onclick="this.select()"></td>
                                <td style="width: 40px;"><input name="load2" type="text" value="<?= $c['load2'] ?>"
                                                                style="width: 50px;!important"
                                                                onclick="this.select()"></td>
                            </tr>
                        <?php endforeach; ?>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
</body>
<script>
    jQuery(document).ready(function () {
        jQuery('.classes td.green').on('click', function () {
            jQuery('.classes td').removeClass('selected');
            jQuery(this).addClass('selected');
            jQuery('.toBeHide').css('display', 'none');
            var select = this
                .innerText
                .split('\n')[0]
                .replace(' ', '_')
                .replace('(', '')
                .replace(')', '');
            jQuery('#' + select).css('display', 'block');
        })
    });
</script>
</html>
