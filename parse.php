<?php

ini_set("auto_detect_line_endings", true); // vital because some files have Windows ending


$nodes = array();
$nodes_map = array();
$edges = array();

$node_count = 0;


$row_count = 0;

$header = array();
$header_lookup = array();

$done = false;

$filename = '2018/eBird_Taxonomy_v2018_14Aug2018.csv';
//$filename = '2017/eBird_Taxonomy_v2017_18Aug2017.csv';

$file = @fopen($filename, "r") or die("couldn't open $filename");		
$file_handle = fopen($filename, "r");
while (!feof($file_handle) && !$done) 
{
	$row = fgetcsv(
		$file_handle, 
		0, 
		',',
		'"'
		);
		
	//print_r($row);		

	$go = is_array($row);
			
	if ($go && ($row_count == 0))
	{
		$header = $row;
		
		$n = count($header);
		for ($i = 0; $i < $n; $i++)
		{
			$header_lookup[$header[$i]] = $i;
		}
		
		
		$go = false;
	}
	if ($go)
	{
		//print_r($row);
		
		$obj = new stdclass;
		
		foreach ($row as $k => $v)
		{
			if ($v != '')
			{
				$obj->{$header[$k]} = $v;
			}
		}
		
		//print_r($obj);
		
		// nodes
		
		
		// $path
		
		$path = [];
		
		/*
 	[TAXON_ORDER] => 7
    [CATEGORY] => slash
    [SPECIES_CODE] => y00934
    [PRIMARY_COM_NAME] => Common/Somali Ostrich
    [SCI_NAME] => Struthio camelus/molybdophanes
    [ORDER1] => Struthioniformes
    [FAMILY] => Struthionidae (Ostriches)
    [REPORT_AS] => y00934	
    	*/	
		
		$path[] = 'Aves';
		
		if (isset($obj->ORDER1))
		{
			$path[] = $obj->ORDER1;
		}
		
		if (isset($obj->FAMILY))
		{
			$family = $obj->FAMILY;
			$family = preg_replace('/\s+\(.*$/', '', $family);
			$path[] = $family;
		}
		
		if (isset($obj->SCI_NAME))
		{
			$s = $obj->SCI_NAME;
			$s = preg_replace('/\s+\(.*$/', '', $s);
			$s = preg_replace('/ Group/i', '-Group', $s);
			$s = preg_replace('/undescribed form/', 'undescribed-form', $s);
			
			$parts = explode(' ', $s);
			
			$n = count($parts);
			$x = array();
			for ($i = 0; $i < $n-1; $i++)
			{
				$x[] = $parts[$i];
				
				$str = join(' ', $x);
				
				/*
				if ($i == $n - 1)
				{
					$str .= ' ' . $obj->SPECIES_CODE;
				}
				*/
				
				if ($str != $path[count($path)-1])
			    {
					$path[] = $str;
				}
			}
			
		
			/*
			$genus = '';
		
			if (preg_match('/^(?<genus>.*)\s+/U', $obj->SCI_NAME, $m))
			{
				$genus = $m['genus'];
			}
			else
			{
				print_r($obj);
				exit();
			}
		
			if ($genus != '')
			{
			    if ($genus != $path[count($path)-1])
			    {
					$path[] = $genus;
				}
			}
			*/
			$path[] = $obj->SPECIES_CODE;
		}
		else
		{		
			$path[] = $obj->SPECIES_CODE;
		}
		
		// echo "Path\n";
		//print_r($path);
		
		$n = count($path);
		
		for ($i = 0; $i < $n; $i++)
		{
			if (!isset($nodes_map[$path[$i]]))  
			{
				$nodes[$node_count] = $path[$i];
				$nodes_map[$path[$i]] = $node_count;
				$node_count++;	
			}
		}
		
		
		for ($i = ($n - 1); $i > 0; $i--)
		{
			//if (in_array($nodes_map[$path])
			$from 	= $nodes_map[$path[$i]];
			$to 	= $nodes_map[$path[$i-1]];
			
			$edges[$from] = $to;
		
		}
		
		
		// add path (if not already there)
		
		// edges
		// add edges
		
		
	}

	$row_count++;
	
	if ($row_count > 30000) 
	//if ($obj->SPECIES_CODE == 'shoreb1')
	{
		$done = true;
		//exit();
	}
}


// dump tree

/*
echo "Nodes\n";
print_r($nodes);
echo "Nodes map\n";
print_r($nodes_map);
echo "Edges\n";
print_r($edges);
*/

echo "graph [\n";
echo "directed 1\n";

foreach ($nodes as $k => $v)
{
	echo "node [";
	echo "  id " . $k . "";
	echo "  label \"" . addcslashes($v, '"') . "\"";
	echo " ]\n";
}

foreach ($edges as $k => $v)
{
	echo "edge [";
	echo "  source $v";
	echo "  target $k";
	echo " ]\n";
}


echo "]\n";

?>
