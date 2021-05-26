<?php
namespace App\Model;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;

class ConsoleTools
{



    public static function makeStyles(OutputInterface $output)
    {

        $valStyle = new OutputFormatterStyle('yellow', 'black', array(
            'bold'
        ));
        $output->getFormatter()->setStyle('value', $valStyle);

        $couStyle = new OutputFormatterStyle('yellow', 'blue', array(
            'bold'
        ));
        $output->getFormatter()->setStyle('summary', $couStyle);

        $couStyle = new OutputFormatterStyle('green', 'black', array());
        $output->getFormatter()->setStyle('code', $couStyle);

        $couStyle = new OutputFormatterStyle('red', 'black', array());
        $output->getFormatter()->setStyle('error', $couStyle);

        $couStyle = new OutputFormatterStyle('green', null, array());
        $output->getFormatter()->setStyle('filename', $couStyle);
    }


}