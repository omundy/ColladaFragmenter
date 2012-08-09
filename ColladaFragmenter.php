<?php

/**
 *	collada-fragmenter by Owen Mundy owenmundy.com and Joelle Dietrick joelledietrick.com
 *	Parse a collada file and export a set number of geometries
 *	PHP 5.3+ recommended, SimpleXML support required
 */

$file = "import/UF342 longitudinal section perspective.dae";
//$file = "import/documenta_01/bauhaus_office.dae";

colladaFragmenter($file, 44, 53, true, false, 'export/');


/**
 *	colladaFragmenter()
 *
 *	@param $file String The COLLADA (.dae) file to use
 *	@param $start Int	Start from nth geometry
 *						 0 = start with 1st geometry
 *						 9 = start with 10th geometry
 *						-1 = start with last geometry and go backwards
 *	@param $end Int 	Stop on nth geometry
 *	@param $rand Bool 	Whether or not geometries will be chosen at random
 *						 - true = choose fragments randomly
 *						 - false = choose fragments in sequence
 *	@param $save Bool	Whether or not to save COLLADA to a file
 *	@param $show Bool	Whether or not to show contents of COLLADA
 */
function colladaFragmenter($file, $start, $end, $save=false, $show=true, $export_dir='export/')
{
	$xml_all = array(); 		// reference for choosing which nodes to include
	$xml_geometries = array(); 	// array to hold original id => number of geometries
	$xml_nodes = array(); 		// array to hold original id => number of nodes
	$geometries_out = '';
	$nodes_out = '';
	$xml = array(); 			// new XML to output
	$count = 0; 		// counter
	$filename_append = '';
	
	// get file
	if ($contents = file_get_contents($file))
	{
		// store as XML
		if ($collada = new SimpleXMLElement($contents))	
		{
			// Since we're only interested in the geometries from the Collada file,
			// we store all geometries, and then use the ids of the geometries to 
			// create the matching nodes within visual_scenes.
			for ($i=$start; $i<$end; $i++)
			{
				if ($geometry = $collada->library_geometries->geometry[$i])
				{
					$geometries_out .= $geometry->asXML();	// store geometries as XML strings
					
					$id = htmlentities((string)$geometry['id']);	// store id
					
					$nodes_out .= '<node id="'.$id.'" name="'.$id.'">';
					$nodes_out .= '<instance_geometry url="#'.$id.'">';
					$nodes_out .= '<bind_material><technique_common/></bind_material>';
					$nodes_out .= '</instance_geometry>';
					$nodes_out .= '</node>';
					
					$count++; // increment counter
					//print $i .': '. $id . "\n"; // testing
				}
				else
				{
					print "\nrequest exceeds available geometries";	
					$last = $i;
					break;
				}
			}
			
			/*
			// testing
			print_r($geometries_out);
			print $collada->library_geometries->geometry[101]['id']->asXML() ."\n";
			*/
			
			
			// collada header
			$xml[] = '<?xml version="1.0" encoding="utf-8"?>';
			$xml[] = '<COLLADA xmlns="http://www.collada.org/2005/11/COLLADASchema" version="1.4.1">';
			$xml[] = '<asset><contributor><authoring_tool>Collada-Fragmenter</authoring_tool></contributor>';
			$xml[] = '<created>'.date("c").'</created>';
			$xml[] = '<unit meter="0.03" name="inch" /><up_axis>Z_UP</up_axis></asset>';			

			// scene
			$xml[] = '<library_visual_scenes>';
			$xml[] = '<visual_scene id="collada_fragmenter" name="collada_fragmenter">';
			$xml[] = $nodes_out;
			$xml[] = "</visual_scene>";
			$xml[] = "</library_visual_scenes>";
			
			// geometries
			$xml[] = "<library_geometries>";
			$xml[] = $geometries_out;
			$xml[] = "</library_geometries>";
			
			// end of file
			$xml[] = "<scene><instance_visual_scene url='#collada_fragmenter' /></scene>";
			$xml[] = "</COLLADA>";			// finish collada
			$xml_out = implode("\n",$xml);	// and put it back into a string	
		
			if ($save == true)
			{
				// create a filename with the date
				//$filename = "f_" . date("Ymd") .'_'. $start .'-'. $end . $filename_append . ".dae";
				if ($f = explode('/',$file)){
					$exportfile = str_replace('.dae','',$f[count($f)-1]);
				}
				$exportfile = $exportfile .'_'. $start .'-'. $end . $filename_append . ".dae";
				
				// create new SimpleXML using the string
				$sxe = new SimpleXMLElement($xml_out);
		
				// use DOM to format XML
				$dom = new DOMDocument('1.0');
				$dom->preserveWhiteSpace = false;
				$dom->formatOutput = true;
				$dom->loadXML($sxe->asXML());
				//echo $dom->saveXML();					// testing
				$dom->save( $export_dir . $exportfile );	// save using DOM
				//$sxe->asXML("export/$filename"); 		// save using SimpleXML
			}
			if ($show == true){
				echo $xml_out;
			}
			
			
			// REPORT
			print "\n\n------------------------------------------------------------------\n\n";
			
			print "$ import file \t= $file\n";
			
			print "$ geometries requested \t= $start-$end\n";
			print "$ total geometries \t= " . $collada->library_geometries->geometry->count() . "\n";
			print "$ geometries exported \t= " . $count . "\n";
			
			print "$ export file \t= " . $exportfile . "";

			print "\n\n------------------------------------------------------------------\n\n";
		}
	}
}

?>