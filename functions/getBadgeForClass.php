<?php
if ( ! function_exists('getBadgeForClass'))
{
    /**
     * @param array $data
     * @param string $class
     * @return string
     */
    function getBadgeForClass($data,$class)
    {
        if (!key_exists($class,$data)) return '<br>';
        $items = $GLOBALS['items'];
        $check = end($data[$class]);
        if (in_array('Inspirit',$check['hero']) || in_array('Inspirit',$check['stash'])) {
            $insp = '<span class="badge badge-secondary">Insp</span>';
        } else {
            $insp = '';
        }
        $godly = '';
        foreach (array_merge($check['hero'],$check['stash']) as $item) {
            if ($items[preg_replace('/ \(.*\)/', '',$item)][3] === 'IMP4') {
                $godly = '<span class="badge badge-secondary">Godly</span>';;
            }
        }
        $color = '';
        if ($check['lvl'] === 300) {
            $color = ' style="background-color: lightcoral;" ';
        }
        return '<br><span class="badge badge-secondary" '.$color.'>'.$check['lvl'].'</span>'.$insp.$godly;
    }
}