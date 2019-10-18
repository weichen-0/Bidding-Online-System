<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>BIOS JSON Authentication Form</h1>
        <form method='POST' action='authenticate.php'>
            <table>
                <tr>
                    <td>User ID</td>
                    <td>
                        <input name='username'/>
                    </td>
                </tr>
                <tr>
                    <td>Password</td>
                    <td>
                        <input name='password' type='password'/>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input name='Login' type='submit'/>
                    </td>
                </tr>
            </table>             
        </form>

    </body>
</html>