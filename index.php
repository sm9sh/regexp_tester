<?php
function var_dump2var($mixed = null) {
  ob_start();
  var_dump($mixed);
  $content = ob_get_contents();
  ob_end_clean();
  return $content;
}

if (isset($_POST) && isset($_POST['pattern']) && isset($_POST['subject']) && $_POST['pattern'] != "" && $_POST['subject'] != "" && isset($_POST['func']))
{
	switch ($_POST['func'])
	{
		case "preg_match":
			$return = preg_match($_POST['pattern'], $_POST['subject'], $matches);
			break;
		case "preg_match_all":
			$return = preg_match_all($_POST['pattern'], $_POST['subject'], $matches);
			break;
		case "preg_replace":
			$return = preg_replace($_POST['pattern'], $_POST['replacement'], $_POST['subject']);
			$matches = "";
			break;
		default:
			$return = false;
			break;
	}
	$json_arr['return'] = var_dump2var($return);
	$json_arr['matches'] = var_dump2var($matches);
	exit(json_encode($json_arr));
}

?>

<!DOCTYPE html>
<html>
	<head>
		<title>Regexp test</title>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<script src="jquery.js"></script>
	</head>
	<body>
		<h2>Regexp test</h2>
		<div style="display: inline-block;">
			<div>
				<div style="display: inline-block;">
					<fieldset>
						<legend>PHP</legend>
						<label form="form1"><input name="func" class="func php" type="radio" value="preg_match" checked>preg_match</label>
						<label form="form1"><input name="func" class="func php" type="radio" value="preg_match_all">preg_match_all</label>
						<label form="form1"><input name="func" class="func php" type="radio" value="preg_replace">preg_replace</label>
					</fieldset>
				</div>
				<div style="display: inline-block;">
					<fieldset>
						<legend>Javascript</legend>
						<label><input name="func" class="func js" type="radio" value="match">.match</label>
						<label><input name="func" class="func js" type="radio" value="test">.test</label>
						<label><input name="func" class="func js" type="radio" value="replace">.replace</label>
					</fieldset>
				</div>
			</div>
			<div style="display: inline-block; padding: 20px 20px 20px 0;">
				<form name="form1" method=post>
					<fieldset>
						<legend>PHP preg_match ( string $pattern , string $subject [, array &$matches [, int $flags = 0 [, int $offset = 0 ]]] )</legend>
							pattern:<br><input id="pattern" type="text" value="" placeholder="/^foo/" style="width: 100%;"><br><br>
							<div id="replace_field">
								replacement:<br><input id="replacement" type="text" value="" placeholder="" style="width: 100%;"><br><br>
							</div>
							subject:<br><textarea id="subject" type="text" value="" placeholder="foofighters fools" style="width: 100%;"></textarea>
							<br><br>
					</fieldset>
				</form>
			</div>
		</div>
		<div style="display: inline-block; vertical-align: top;">
			<fieldset>
				<legend><b>RESULT</b></legend>
				<b>return</b>: <pre id="return"></pre><br><br>
				<b><span id="result2">matches</span></b>: <pre id="matches"></pre>
				<br><br>
			</fieldset>
		</div>
		<div style="font-size: 20px">
			<div class="preg_match php doc">
				int <b>preg_match</b> ( string <b>$pattern</b> , string <b>$subject</b> [, array <b>&$matches</b> [, int <b>$flags</b> = 0 [, int <b>$offset</b> = 0 ]]] )
			</div>
			<div class="preg_match_all php doc">
				int <b>preg_match_all</b> ( string <b>$pattern</b> , string <b>$subject</b> [, array <b>&$matches</b> [, int <b>$flags</b> = PREG_PATTERN_ORDER [, int <b>$offset</b> = 0 ]]] )
			</div>
			<div class="preg_replace php doc">
				mixed <b>preg_replace</b> ( mixed <b>$pattern</b> , mixed <b>$replacement</b> , mixed <b>$subject</b> [, int <b>$limit</b> = -1 [, int <b>&$count</b> ]] )
			</div>
			<div class="match js doc">
				str.match(regexp)
			</div>
			<div class="test js doc">
				regexObj.test(str)
			</div>
			<div class="replace js doc">
				str.replace(regexp|substr, newSubStr|function[,  flags]);
			</div>
		</div>
	</body>
	<script>
		jQuery(document).ready(function()
		{
			func_el = null;
			func = '';

			function check_func()
			{
				func_el = $('input.func:checked');
				func = func_el.val();
				if (func == 'preg_replace' || func == 'replace')
				{
					$('#replace_field').show();
				} else
				{
					$('#replace_field').hide();
				}
				
				$('#result2').text(func_el && func_el.hasClass('php') ? 'matches' : 'command string');
				$('.doc').hide();
				$('.doc.' + func).show();
			}

			function proceed_data()
			{
				var pattern = $('#pattern').val();
				var subject = $('#subject').val();
				var replacement = $('#replacement').val();

				if (!pattern || !subject || !func) {
					return;
				}
				
				if (func_el && func_el.hasClass('php'))
				{
					$.post("",
						{
							'pattern': pattern,
							'subject': subject,
							'replacement': replacement,
							'func': func,
						},
						function(data) {
							data = jQuery.parseJSON(data);
							$('#return').text(data.return);
							$('#matches').text(data.matches);
					});
				} else if (func_el && func_el.hasClass('js'))
				{
					var res, cmd = '';
					var match = pattern.replace(/\/(.*)\/(.*)/, '$1|$2').split('|');
					pattern = match[0];
					var flags = match[1];
					switch (func)
					{
						case 'match':
							res = var_dump(subject.match(new RegExp(pattern, flags)));
							$('#return').text(res);
							cmd = '"' + subject + '".' + func + '(/' + pattern + '/' + flags + ')';
							break;
						case 'test':
							var reg = new RegExp(pattern, 'g');
							res = var_dump(reg.test(subject));
							$('#return').text(res);
							cmd = '/' + pattern + '/.' + func + '("' + subject + '")';
							break;
						case 'replace':
							res = var_dump(subject.replace(new RegExp(pattern, flags), replacement));
							$('#return').text(res);
							cmd = '"' + subject + '".' + func + '(/' + pattern + '/' + flags + ', "' + replacement + '")';
							break;
					}
					$('#matches').text(cmd);
				}
			}

			$('#pattern, #subject, #replacement').on('input keyup change focus', function()
			{
				proceed_data();
			});

			$('input.func').on('click select change load', function()
			{
				check_func();
				proceed_data();
			});

			check_func();
			$('.doc').hide();
			$('.doc.' + func).show();



			function var_dump () {
				var output = "", pad_char = " ", pad_val = 4, lgth = 0, i = 0, d = this.window.document;
				var getFuncName = function (fn) {
					var name = (/\W*function\s+([\w\$]+)\s*\(/).exec(fn);
					if (!name) {
						return '(Anonymous)';
					}
					return name[1];
				};
				var repeat_char = function (len, pad_char) {
					var str = "";
					for (var i=0; i < len; i++) {             
						str += pad_char;         
					}         
					return str;     
				};     
				var getScalarVal = function (val) {         
					var ret = '';         
					if (val === null) {
						 ret = 'NULL';
					 } else if (typeof val === 'boolean') {
						 ret = 'bool('+val+')';
					 } else if (typeof val === 'string') {
						 ret = 'string('+val.length+') "'+val+'"';
					 } else if (typeof val === 'number') {
						 if (parseFloat(val) == parseInt(val, 10)) {
							 ret = 'int('+val+')';
						 } else {
							ret = 'float('+val+')';
						 }
					 } else if (val === undefined) {
						 ret = 'UNDEFINED'; // Not PHP behavior, but neither is undefined as value
					 }  else if (typeof val === 'function') {
						 ret = 'FUNCTION'; // Not PHP behavior, but neither is function as value
						 ret = val.toString().split("\n");
						 txt = "";
						 for(var j in ret) {
							 txt+= (j !=0 ? thick_pad : '')+ret[j]+"\n"; 
						 }
						 ret = txt;
					} else if (val instanceof Date) {
						 val = val.toString();
						 ret = 'string('+val.length+') "'+val+'"'
					 }
					 else if(val.nodeName) {
						 ret = 'HTMLElement("'+val.nodeName.toLowerCase()+'")';
					 }
					 return ret;
				 };
				 var formatArray = function (obj, cur_depth, pad_val, pad_char) {
					 var someProp = '';
					 if (cur_depth > 0) {
						cur_depth++;
					}
					base_pad = repeat_char(pad_val*(cur_depth-1), pad_char);
					thick_pad = repeat_char(pad_val*(cur_depth+1), pad_char);
					var str = "";
					var val='';
					if (typeof obj === 'object' && obj !== null) {
						if (obj.constructor && getFuncName(obj.constructor) === 'PHPJS_Resource') {
							return obj.var_dump();
						}
						lgth = 0;
						for (someProp in obj) {
							lgth++;
						}
						str += "array("+lgth+") {\n";
						for (var key in obj) {
							if (typeof obj[key] === 'object' && obj[key] !== null && !(obj[key] instanceof Date) && !obj[key].nodeName) {
								str += thick_pad + "["+key+"] =>\n"+thick_pad+formatArray(obj[key], cur_depth+1, pad_val, pad_char);
							} else {
								val = getScalarVal(obj[key]);
								str += thick_pad + "["+key+"] =>\n"+  thick_pad +val + "\n";
							}
						}
						str += base_pad + "}\n";
					} else {
						str = getScalarVal(obj);
					}
					return str;
				};
				output = formatArray(arguments[0], 0, pad_val, pad_char);
				for ( i=1; i < arguments.length; i++ ) {
					output += '\n' + formatArray(arguments[i], 0, pad_val, pad_char);
				}
				return output;
			}
		});
	</script>
</html>