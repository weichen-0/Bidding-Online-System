<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>BIOS JSON Drop Section Form</h1>
        <form method='POST' action='drop-section.php'>
            <table>
                <tr>
                    <td>User ID</td>
                    <td>
                        <input name='username'/>
                    </td>
                </tr>
                <tr>
                    <td>Course</td>
                    <td>
                        <input name='course'/>
                    </td>
                </tr>
                <tr>
                    <td>Section</td>
                    <td>
                        <input name='section'/>
                    </td>
                </tr>
                <tr>
                    <td>Token</td>
                    <td>
                    <input name='token' value='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImJlbi5uZy4yMDA5IiwiZGF0ZXRpbWUiOiIyMDE5LTA5LTI0IDA1OjU5OjIzIn0.qUIO2wAznusFU7xF7nIAXwbprnyUiW12oGdk7MdRbpI'/>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input name='Drop Section' type='submit'/>
                    </td>
                </tr>
            </table>             
        </form>

    </body>
</html>