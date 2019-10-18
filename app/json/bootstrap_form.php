<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>BIOS JSON Bootstrap Form</h1>
        <form action="bootstrap.php"  method="post" enctype="multipart/form-data">
            <table>
                <tr>
                    <td>Zip File Upload</td>
                    <td><input type="file" name="bootstrap-file" /></td>
                </tr>
                <tr>
                    <td>Token</td>
                    <td><input type='text' name='token' value='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImJlbi5uZy4yMDA5IiwiZGF0ZXRpbWUiOiIyMDE5LTA5LTI0IDA1OjU5OjIzIn0.qUIO2wAznusFU7xF7nIAXwbprnyUiW12oGdk7MdRbpI' /></td>
                    <!-- substitute the above value with a valid token -->
                </tr>
                <tr>
					<td colspan='2' style="text-align:left"><input name='import' type='submit' /></td>
            	</tr>
            </table>
        </form>

    </body>
</html>

