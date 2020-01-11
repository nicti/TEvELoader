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

        .gold {
            background-color: gold;
            cursor: pointer;
        }

        .reset-cursor {
            cursor: default!important;
        }

        .tooltip {
            position: relative;
            display: inline-block;
            border-bottom: 1px dotted black;
        }

        .tooltiptext {
            width: 300px!important;
            text-align: left!important;
            padding-left: 5px!important;
        }

        .tooltip .tooltiptext {
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

        .tooltip .tooltiptext::after {
            content: "";
            position: absolute;
            top: 100%;
            left: 20%;
            margin-left: -5px;
            border-width: 5px;
            border-style: solid;
            border-color: #555 transparent transparent transparent;
        }

        .tooltip:hover .tooltiptext {
            visibility: visible;
            opacity: 1;
        }
    </style>
    <script type="text/javascript" src="assets/jquery-3.4.1.min.js"></script>
</head>
<body style="background-color: #373737">
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
<table style="margin-bottom: 10px;">
    <tr>
        <td class="red reset-cursor">no code</td>
        <td class="green reset-cursor">code</td>
        <td class="gold reset-cursor">lvl 300</td>
    </tr>
</table>
<table class="classes">
    <tr>
        <td colspan="11" class="<?= getColorForClass($data, 'Novice (Male)') ?>">Novice (Male)</td>
        <td colspan="7" class="<?= getColorForClass($data, 'Novice (Female)') ?>">Novice (Female)</td>
    </tr>
    <tr>
        <td colspan="2" class="<?= getColorForClass($data, 'Swordsman') ?>">Swordsman</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Initiate') ?>">Initiate</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Thief') ?>">Thief</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Witch Hunter') ?>">Witch Hunter</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Druid') ?>">Druid</td>
        <td class="<?= getColorForClass($data, 'Acolyte (Male)') ?>">Acolyte (Male)</td>
        <td class="<?= getColorForClass($data, 'Acolyte (Female)') ?>">Acolyte (Female)</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Archer') ?>">Archer</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Templar') ?>">Templar</td>
    </tr>
    <tr>
        <td colspan="2" class="<?= getColorForClass($data, 'Knight') ?>">Knight</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Mage') ?>">Mage</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Rogue') ?>">Rogue</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Slayer') ?>">Slayer</td>
        <td colspan="2" class="<?= getColorForClass($data, 'ArchDruid') ?>">ArchDruid</td>
        <td class="<?= getColorForClass($data, 'Cleric (Male)') ?>">Cleric (Male)</td>
        <td class="<?= getColorForClass($data, 'Cleric (Female)') ?>">Cleric (Female)</td>
        <td colspan="2" class="<?= getColorForClass($data, 'Hunter') ?>">Hunter</td>
        <td colspan="2" class="<?= getColorForClass($data, 'ArchTemplar') ?>">ArchTemplar</td>
    </tr>
    <tr>
        <td class="<?= getColorForClass($data, 'Crusader') ?>">Crusader</td>
        <td class="<?= getColorForClass($data, 'Imperial Knight') ?>">Imperial Knight</td>
        <td class="<?= getColorForClass($data, 'Wizard') ?>">Wizard</td>
        <td class="<?= getColorForClass($data, 'Sage') ?>">Sage</td>
        <td class="<?= getColorForClass($data, 'Assassin') ?>">Assassin</td>
        <td class="<?= getColorForClass($data, 'Stalker') ?>">Stalker</td>
        <td class="<?= getColorForClass($data, 'Witcher') ?>">Witcher</td>
        <td class="<?= getColorForClass($data, 'Inquisitor') ?>">Inquisitor</td>
        <td class="<?= getColorForClass($data, 'Shaman') ?>">Shaman</td>
        <td class="<?= getColorForClass($data, 'Shapeshifter') ?>">Shapeshifter</td>
        <td class="<?= getColorForClass($data, 'Priest') ?>">Priest</td>
        <td class="<?= getColorForClass($data, 'Matriarch') ?>">Matriarch</td>
        <td class="<?= getColorForClass($data, 'Marksman') ?>">Marksman</td>
        <td class="<?= getColorForClass($data, 'Tracker') ?>">Tracker</td>
        <td class="<?= getColorForClass($data, 'High Templar') ?>">High Templar</td>
        <td class="<?= getColorForClass($data, 'Dark Templar') ?>">Dark Templar</td>
    </tr>
    <tr>
        <td class="<?= getColorForClass($data, 'Avenger') ?>">Avenger</td>
        <td class="<?= getColorForClass($data, 'Champion') ?>">Champion</td>
        <td class="<?= getColorForClass($data, 'White Wizard') ?>">White Wizard</td>
        <td class="<?= getColorForClass($data, 'ArchSage') ?>">ArchSage</td>
        <td class="<?= getColorForClass($data, 'Phantom Assassin') ?>">Phantom Assassin</td>
        <td class="<?= getColorForClass($data, 'Master Stalker') ?>">Master Stalker</td>
        <td class="<?= getColorForClass($data, 'Professional Witcher') ?>">Professional Witcher</td>
        <td class="<?= getColorForClass($data, 'Grand Inquisitor') ?>">Grand Inquisitor</td>
        <td class="<?= getColorForClass($data, 'Summoner') ?>">Summoner</td>
        <td class="<?= getColorForClass($data, 'RuneMaster') ?>">RuneMaster</td>
        <td class="<?= getColorForClass($data, 'Hierophant') ?>">Hierophant</td>
        <td class="<?= getColorForClass($data, 'Prophetess') ?>">Prophetess</td>
        <td class="<?= getColorForClass($data, 'Sniper') ?>">Sniper</td>
        <td class="<?= getColorForClass($data, 'Monster Hunter') ?>">Monster Hunter</td>
        <td class="<?= getColorForClass($data, 'Grand Templar') ?>">Grand Templar</td>
        <td class="<?= getColorForClass($data, 'Dark ArchTemplar') ?>">Dark ArchTemplar</td>
    </tr>
</table>
<?php
foreach ($data as $class => $info):?>
    <div id="<?= str_replace([' ', '(', ')'], ['_', '', ''], $class) ?>" class="toBeHide" style="display: none;">
        <fieldset>
            <legend style="color: white"><?= $class ?></legend>
            <table style="color: white">
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
                        <td <?php if ($c['lvl']===300){echo 'class="gold reset-cursor"';}?>><?= $c['lvl']; ?></td>
                        <td><?= number_format($c['g']); ?></td>
                        <td><?= $c['s']; ?></td>
                        <?php foreach ($c['hero'] as $i): ?>
                            <td>
                                <div class="tooltip"><?= $i ?>
                                    <?php $i = preg_replace('/ \(.*\)/','',$i);?>
                                    <span class="tooltiptext"><?= $items[$i][0]; ?><br><?= $items[$i][1] ?><br><?= str_replace('.', "<br>", $items[$i][2]); ?></span>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <?php foreach ($c['stash'] as $i): ?>
                            <td>
                                <div class="tooltip"><?= $i ?>
                                    <?php $i = preg_replace('/ \(.*\)/','',$i);?>
                                    <span class="tooltiptext"><?= $items[$i][0]; ?><br><?= $items[$i][1] ?><br><?= str_replace('.', "<br>", $items[$i][2]); ?></span>
                                </div>
                            </td>
                        <?php endforeach; ?>
                        <td><input name="load" type="text" value="<?= $c['load'] ?>" onclick="this.select()"></td>
                        <td><input name="load2" type="text" value="<?= $c['load2'] ?>" onclick="this.select()"></td>
                    </tr>
                <?php endforeach; ?>
            </table>
        </fieldset>
    </div>
<?php endforeach; ?>
</body>
<script>
    jQuery(document).ready(function () {
        jQuery('.classes td').on('click', function () {
            jQuery('.classes td').css('border','1px solid black');
            jQuery(this).css('border','2px solid blue');
            jQuery('.toBeHide').css('display', 'none');
            jQuery('#' + this.innerHTML.replace(' ', '_').replace('(', '').replace(')', '')).css('display', 'block');
        })
    });
</script>
</html>
