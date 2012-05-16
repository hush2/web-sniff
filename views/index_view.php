<!doctype html>
<html>

<head>
    <title>Web-Sniff v1.33.7</title>
    <link rel='stylesheet' href='css/style.css' type='text/css'>
</head>

<body>
    <p id='logo'>HTTP Web-Sniff v1.33.7 by hush2</p>

    <h1>View HTTP Request and Response Header</h1>

    <form action='.' method='post'>
    <fieldset>

    <legend>For more information on HTTP see <a href='rfc/rfc2616.html'>RFC 2616</a></legend>

    <p><b>HTTP URL:</b>
        <input class='text url' name='url' value='<?= $post->url ?>' />
        <input type='submit' name='submit' value='Submit' />
    </p>
    <p>HTTP version:
    <input type='radio' <?= $post->http == '1.1' ? 'checked' : '' ?> name='http' value='1.1' /> HTTP/1.1
    <input type='radio' <?= $post->http == '1.0' ? 'checked' : '' ?> name='http' value='1.0' /> HTTP/1.0 (with Host header)
    </p>
    <p>
    <input type='checkbox' <?= $post->raw ? 'checked' : '' ?> name='raw' value='1' /> Raw HTML view
    <input type='checkbox' <?= $post->gzip ? 'checked' : '' ?> name='gzip' value='1' /> Accept-Encoding: gzip |
    Request type:
    <input type='radio' <?= $post->type == 'get'  ? 'checked' : ''?> name='type' value='get' /> GET
    <input type='radio' <?= $post->type == 'head' ? 'checked' : ''?> name='type' value='head' /> HEAD
    </p>
    <p>User Agent:
    <select name='ua'>
        <option <?= $ua[0] ?>value='0'>Web-Sniff</option>
        <option <?= $ua[1] ?>value='1'>Internet Explorer 6</option>
        <option <?= $ua[2] ?>value='2'>Internet Explorer 7</option>
        <option <?= $ua[3] ?>value='3'>Firefox 3</option>
        <option <?= $ua[4] ?>value='4'>Google Chrome 5</option>
        <option <?= $ua[5] ?>value='5'>Safari 5</option>
        <option <?= $ua[6] ?>value='6'>iPhone Mobile Safari</option>
        <option <?= $ua[7] ?>value='7'>Netscape 4.8</option>
        <option <?= $ua[8] ?>value='8'>Opera 9.2</option>
        <option <?= $ua[9] ?>value='9'>Googlebot</option>
        <option <?= $ua[10] ?>value='10'>none</option>
    </select>
    </p>

    </fieldset>
    </form>

<?php if ($post->submit): ?>

    <h2>HTTP Request Header</h2>

<?php if (!isset($error)): ?>
<pre>
<?= $conn_msg ?>


<?= $request_headers ?>
</pre>

    <h2>HTTP Response Header</h2>

    <table class='tbl'>
        <tr><th>Name</th><th>Value</th></tr>
        <tr><td><b>Status</b></td><td><?= $response_status ?></td>
        </tr>
        <?php foreach($response_headers as $name => $value): ?>
            <tr>
            <td class='name'><?php if ($name == 'Location') { $value = "<a href='$value'>$value</a>"; } ?><?= $name ?></td>
            <td><?= $value ?></td>
            </tr>
        <?php endforeach ?>
    </table>

    <?php if (!empty($response_body)): ?>

    <h2>Body (<?= $content_length ?>)</h2>

    <div id='html'>
        <pre><?= $response_body ?></pre>
    </div>

    <?php endif ?>

<? else: ?>

    <p><?= $conn_msg ?><p>
    <p><?= $err_msg ?><p>

<? endif ?>
<? endif ?>

</body>
</html>
