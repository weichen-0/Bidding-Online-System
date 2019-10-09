<html>
    <head>
        <link rel="stylesheet" type="text/css" href="../include/style.css">
    </head>
    <body>
        <h1>BIOS JSON Delete Bid Form</h1>
        <form method='POST' action='delete-bid.php'>
            <table>
                <tr>
                    <td>User ID</td>
                    <td>
                        <input name='userid' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td>Course</td>
                    <td>
                        <input name='course' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td>Section</td>
                    <td>
                        <input name='section' type='text'/>
                    </td>
                </tr>
                <tr>
                    <td>Token</td>
                    <td>
                        <input name='token' value='eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJ1c2VybmFtZSI6ImFkbWluIiwiZGF0ZXRpbWUiOiIyMDE5LTEwLTA5IDAzOjE4OjM2In0.5lQBoMJcxCezkcvjTkHfiN2ZvYi1tIY4HhXTtB2FV-Q'/>
                    </td>
                </tr>
                <tr>
                    <td colspan='2'>
                        <input name='Delete Bid' type='submit'/>
                    </td>
                </tr>
            </table>             
        </form>

    </body>
</html>