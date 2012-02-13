<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
/*
========================================================
 
--------------------------------------------------------
 Copyright: Andrew Smiley
 http://proteanweb.com
--------------------------------------------------------
 This addon may be used free of charge. Should you
 employ it in a commercial project of a customer or your
 own I'd appreciate a small donation.
========================================================
 File: pi.columnbo.php
--------------------------------------------------------
 Purpose: Display data in columns for lists and tables, 
 * 		  with entries spread vertically or horizontally.
========================================================
 THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF
 ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT 
 LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
 FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO 
 EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE
 FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN
 AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE 
 OR OTHER DEALINGS IN THE SOFTWARE.
=========================================================

*/


$plugin_info = array(
                        'pi_name'			=> 'Column-bo',
                        'pi_version'		=> '1.3',
                        'pi_author'			=> 'Andrew Smiley',
                        'pi_author_url'		=> 'http://proteanweb.com',
                        'pi_description'	=> 'Display data in columns for lists and tables, with entries spread vertically or horizontally.',
                        'pi_usage'			=>  Columnbo::usage()
                    );


class Columnbo {

    public function __construct(){
  
        $this->EE =& get_instance();
		
    }

	function lists(){
		
		
        $tagdata = trim($this->EE->TMPL->tagdata);
		
		$no_results = $this->EE->TMPL->fetch_param('no_results', '');
		$delimiter = $this->EE->TMPL->fetch_param('delimiter', '<br />');
		
		if (!$tagdata || $tagdata == $delimiter){
			return $no_results;
		}
		
		//grab all the parameters
        $columns = $this->EE->TMPL->fetch_param('columns', 2);
		$direction = strtolower($this->EE->TMPL->fetch_param('direction', 'horizontal'));
		$remove_delimiter = strtolower($this->EE->TMPL->fetch_param('remove_delimiter','no'));
		$open = $this->EE->TMPL->fetch_param('open','');
		$close = $this->EE->TMPL->fetch_param('close','');
		
		//generate openings
		$output = array();
		for($i=0; $i < $columns; $i++){
			$output[$i] = $open;
		}
		
		//remove trailing delimiter
		if (substr($tagdata,-strlen($delimiter)) == $delimiter){
			$tagdata = substr($tagdata,0,-strlen($delimiter));
		}
		
		
		//add the data
      	$rows = explode($delimiter, $tagdata);
		
		if ($direction == 'vertical'){
			
			$height = ceil(count($rows) / $columns); 
			$column_index = 0;
			foreach($rows as $i=>$row){
					
				$output[$column_index] .= $row;
				
				if ($remove_delimiter != 'yes'){
					$output[$column_index] .= $delimiter;	
				}
				
				//move to next column when height is reached
				if (($i+1)%$height == 0){
					$column_index++;
				}
			}
		}
		else {
			foreach($rows as $i=>$row){
				$output[$i%$columns] .= $row;
				
				if ($remove_delimiter != 'yes'){
					$output[$i%$columns] .= $delimiter;	
				}
			}
		}
		
		//generate closings
		for($i=0; $i < $columns; $i++){
			$output[$i] .= $close;
		}
		
		//and the case is solved!
		return implode('', $output);	
	}



	function table(){
		
		//grab all the parameters		
        $tagdata = trim($this->EE->TMPL->tagdata);
		
		$no_results = $this->EE->TMPL->fetch_param('no_results', '');
		$delimiter = $this->EE->TMPL->fetch_param('delimiter', '|');
		
		if (!$tagdata || $tagdata == $delimiter){
			return $no_results;
		}
			
        $columns = max(1,$this->EE->TMPL->fetch_param('columns', 2));
		
		
		$direction = strtolower($this->EE->TMPL->fetch_param('direction', 'horizontal'));
		$delimiter = $this->EE->TMPL->fetch_param('delimiter', '|');
		$overflow = strtolower($this->EE->TMPL->fetch_param('overflow','empty'));
		
		
		$tmpl = array (
		
                    'table_open'          => $this->EE->TMPL->fetch_param('table_open','<table border="0" cellpadding="0" cellspacing="0">'),

					'tbody_open'		  => $this->EE->TMPL->fetch_param('tbody_open','<tbody>'),
					'tbody_close'		  => $this->EE->TMPL->fetch_param('tbody_close','</tbody>'),

                    'row_start'           => $this->EE->TMPL->fetch_param('row_start','<tr>'),
                    'row_end'             => $this->EE->TMPL->fetch_param('row_end','</tr>'),
                    'cell_start'          => $this->EE->TMPL->fetch_param('cell_start','<td>'),
                    'cell_end'            => $this->EE->TMPL->fetch_param('cell_end','</td>'),

                    'table_close'         => $this->EE->TMPL->fetch_param('table_close','</table>')	                    				
         );
		 
		$tmpl['row_alt_start'] = $this->EE->TMPL->fetch_param('row_alt_start',$tmpl['row_start']);
		$tmpl['row_alt_end'] = $this->EE->TMPL->fetch_param('row_alt_end',$tmpl['row_end']);
		$tmpl['cell_alt_start'] = $this->EE->TMPL->fetch_param('cell_alt_start',$tmpl['cell_start']);
	    $tmpl['cell_alt_end'] = $this->EE->TMPL->fetch_param('cell_alt_end',$tmpl['cell_end']);
		 
		$this->EE->load->library('table');

		//remove trailing delimiter
		if (substr($tagdata,-strlen($delimiter)) == $delimiter){
			$tagdata = substr($tagdata,0,-strlen($delimiter));
		}

		//add the data
      	$rows = explode($delimiter, $tagdata);
		
		//need to reorder the columns
		if ($direction == 'vertical'){
			$height = ceil(count($rows) / $columns); 
			$temp = $rows;
			$rows = array();	
			for($i=0; $i < $height; $i++){
				for($j=$i; $j<count($temp); $j+=$height){
					$rows[] = $temp[$j];
				}	
			}
		}

			
		if ($overflow == 'table'){
			
			$remainder = count($rows) % $columns;
			
			if ($remainder != 0){

				$otmpl = array(
				
				  		'table_open'          => $this->EE->TMPL->fetch_param('otable_open',$tmpl['table_open']),
				  		
						'tbody_open'          => $this->EE->TMPL->fetch_param('otbody_open',$tmpl['tbody_open']),
						'tbody_close'         => $this->EE->TMPL->fetch_param('otbody_close',$tmpl['tbody_close']),
	
	                    'row_start'           => $this->EE->TMPL->fetch_param('orow_start',$tmpl['row_start']),
	                    'row_end'             => $this->EE->TMPL->fetch_param('orow_end',$tmpl['row_end']),
	                    'cell_start'          => $this->EE->TMPL->fetch_param('ocell_start',$tmpl['cell_start']),
	                    'cell_end'            => $this->EE->TMPL->fetch_param('ocell_end',$tmpl['cell_end']),
	
	                    'table_close'         => $this->EE->TMPL->fetch_param('otable_close',$tmpl['table_close'])
			 	);
				
				//build overflow table
				$this->EE->table->set_template(str_replace(array('#rem#','#col#'),array($remainder,$columns),$otmpl)); 
				$this->EE->table->add_row(array_slice($rows,-$remainder));
				$otable = $this->EE->table->generate();
				
				$this->EE->table->clear();
				
				//build table
				$this->EE->table->set_template(str_replace(array('#rem#','#col#'),array($remainder,$columns),$tmpl)); 		
				$rows = $this->EE->table->make_columns(array_slice($rows,0,-$remainder),$columns);
				if ($rows){
					foreach($rows as $row){
						$this->EE->table->add_row($row);
					}
				}
				
				//add overflow table to table
				$this->EE->table->add_row(array('data'=>$otable,'colspan'=>$columns));
				
				return $this->EE->table->generate();
			}
		 }

		 //columnbo knew what was going on all along...
		 $this->EE->table->set_template(str_replace(array('#rem#','#col#'),array(0,$columns),$tmpl)); 
		 
		 return $this->EE->table->generate($this->EE->table->make_columns($rows, $columns));

	}

    /* END */

    
// ----------------------------------------
//  Plugin Usage
// ----------------------------------------

// This function describes how the plugin is used.
//  Make sure and use output buffering

function usage() {
ob_start(); 
?>

This plugin is used to display data in lists or a table with a variable number of columns. Of particular mention is the ability to 
specify whether entries move horizontally or vertically throughout the list/table. 

USAGE DETAILS:
==============

TWO TAG TYPES:
----
{exp:columnbo:lists}{/exp:columnbo:lists}

OR 

{exp:columnbo:table}{/exp:columnbo:table}


Depending on the display type, different parameters are available:


LISTS PARAMETERS (with defaults):
----------------------------------

columns = "2" 
Sets the number of columns.

delimiter = "<br />"
This is the string used to separate the data into rows. 
Be sure it won't actually appear within the data itself, or problems will arise that even Columbo can't solve.

direction = "horizontal"
Controls the direction entries move throughout the list.
Acceptable values are "horizontal" and "vertical"

remove_delimiter = "no"
If set to yes, Columbo won't include the delimiter in his final output

open = ""
The markup used to start a new column

close = ""
The markup used to end a column

no_results = ""
What to display if tagdata is empty or only the delimiter is found within the tag pair.


--- Example ---
{exp:columnbo:lists columns="3" open="<ul>" close="</ul>" delimiter="</li>"    }
   {exp:channel:entries channel="murder-suspects"}
   <li>{title}</li>
   {/exp:channel:entries}
{/exp:columnbo:lists}

--- Output ---

<ul>
	<li>Suspect 1</li>
	<li>Suspect 4</li>
</ul>
<ul>
	<li>Suspect 2</li>
	<li>Suspect 5</li>
</ul>
<ul>
	<li>Suspect 3</li>
</ul>


TABLE PARAMETERS (with defaults):
----------------------------------

columns = "2" 
Sets the number of columns.

delimiter = "|"
This is the string used to separate the data into rows. 
Unlike lists, there is no parameter to leave the delimiter in place - its always removed

direction = "horizontal"
Controls the direction entries move throughout the list.
Acceptable values are "horizontal" and "vertical"

overflow = "empty"
When the number of columns doesn't evenly divide the rows, columnbo isn't
sure what to do. When overflow is set to "empty", empty cells are created
to finish the final row.
When overflow="table", the final row is a single spanned column containing a new one-row table 
containing the remaining table cells.

no_results = ""
What to display if tagdata is empty or only the delimiter is found within the tag pair.

Since the CI table library is used to generate the table, the majority of the table template options
are included as parameters with this plugin:

table_open = "<table border="0" cellpadding="0" cellspacing="0">"
tbody_open = "<tbody>"
tbody_close = "</tbody>"
row_start = "<tr>"
row_end = "</tr>"
cell_start = "<td>"
cell_end = "</td>"
row_alt_start = "<tr>"
row_alt_end = "</tr>"
cell_alt_start = "<td>"
cell_alt_end = "</td>"
table_close = "</table>"	

Furthermore, if the overflow parameter is set to "table", you have some additional parameters that
are used to create the embedded table. Ihese values default to the parent table template values. 
otable_open = "<table border="0" cellpadding="0" cellspacing="0">"
otbody_open = "<tbody>"
otbody_close = "</tbody>"
orow_start = "<tr>"
orow_end = "</tr>"
ocell_start = "<td>"
ocell_end = "</td>"
otable_close = "</table>"	


Finally, within the table template parameters' values, you may use the strings "#rem#" and "#col#"
which will be replaced with the column count parameter and the number of remaining columns respectively. 
(#rem# = num_values % columns)



--- Example ---
{exp:columnbo:table columns="3" delimiter="|" direction="vertical" overflow="table" cell_start="<td class='col-#col#'>" ocell_start="<td class='col-#rem#'>"}
	{exp:channel:entries channel="murder-suspects"}
		{title}|
	{/exp:channel:entries}
{/exp:columnbo:table}
--- Output ---

<table border="0" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td class="col-3">Suspect 1</td><td class="col-3">Suspect 3</td><td class="col-3">Suspect 5</td>
		</tr>
		<tr>
			<td colspan="3">
				<table border="0" cellpadding="0" cellspacing="0">
					<td class="col-2">Suspect 2</td><td class="col-2">Suspect 4</td>
				</table>
			</td>
		</tr>
	</tbody>
</table>




CHANGELOG:
==========

1.0
Inital release.

1.1
Bugfix - Columbo would be dissapointed...the second parameter of the "trim" function doesn't specify a 
string to trim off, but a list of characters to trim off! Decided to pop off the last array item after explode, 
since there should always be a trailing delimiter.

1.2 
Added support for the "tbody" table template override
Bugfix - better delimiter stripping to avoid creating a final empty table row
Bugfix - table overflow added duplicate row when total count < column count

1.3
New Feature - no results parameter for when tagdata is empty

1.3.1
Fixed bad documentation example.


<?php
$buffer = ob_get_contents();
	
ob_end_clean(); 

return $buffer;
}
/* END */


}
/* END Class */
/* End of file pi.columnbo.php */
/* Location: ./system/expressionengine/third_party/columnbo/pi.columnbo.php */