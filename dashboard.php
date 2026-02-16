<?php
// Simple dashboard / honor roll.

// 1. Collect all cookie-complete logs.
$logsFile = __DIR__ . '/logs.txt';
$entries  = [];

if (file_exists($logsFile)) {
    $fh = fopen($logsFile, 'r');
    if ($fh) {
        while (($line = fgets($fh)) !== false) {
            $line = trim($line);
            if ($line === '') continue;
            $obj = json_decode($line, true);
            if (!is_array($obj)) continue;

            // we only care about completed cookie sessions
            if (isset($obj['type']) && $obj['type'] === 'cookies_complete') {
                $entries[] = $obj;
            }
        }
        fclose($fh);
    }
}

// newest first
$entries = array_reverse($entries);

// 2. Load 88x31px badge gifs from /badges folder.
$badgeFiles = glob(__DIR__ . '/badges/*.gif');
sort($badgeFiles);

// helper to convert absolute path to relative URL
function rel_url($path) {
    return str_replace(DIRECTORY_SEPARATOR, '/', str_replace(__DIR__ . '/', '', $path));
}

?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <title>honor roll — hyper-max</title>
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <link rel="icon" href="./navigation/favi-eye.png" type="image/svg+xml" />

  <style>
    :root {
      --bg: #cccccc;
      --fg: #000000;
    }

    * {
      box-sizing: border-box;
    }

    html, body {
      height: 100%;
    }

    body {
      margin: 0;
      background: var(--bg);
      color: var(--fg);
      font-family: serif;
      font-size: 16px;
      display: flex;
      flex-direction: column;
    }

    .italic {
      font-style: italic;
    }

    #intro {
      position: fixed;
      top: 16px;
      left: 16px;
      max-width: 340px;
      font-size: 18px;
      z-index: 10;
    }

    #wall {
      position: absolute;
      inset: 0;
      padding: 80px 24px 24px 24px;
      display: flex;
      flex-wrap: wrap;
      align-content: flex-start;
      gap: 10px;
    }

    .badge {
      position: relative;
      width: 88px;
      height: 31px;
      overflow: visible;
    }

    .badge img {
      display: block;
      width: 88px;
      height: 31px;
      image-rendering: pixelated;
      border: 1px solid rgba(0,0,0,0.2);
    }

    .badge-info {
      position: absolute;
      left: 0;
      bottom: 100%;
      transform: translateY(-4px);
      padding: 4px 6px;
      background: rgba(0,0,0,0.85);
      color: #f1f1f1;
      font-size: 10px;
      line-height: 1.3;
      white-space: nowrap;
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.15s ease-in-out;
    }

    .badge:hover .badge-info {
      opacity: 1;
    }

    .empty {
      margin-top: 120px;
      margin-left: 24px;
      font-size: 14px;
      opacity: 0.7;
    }
  </style>
</head>
<body>
  <div id="intro">
    <span class="italic">Honor roll of attentive visitors.</span><br />
    Each valued visitor who completes the consent scroll is awarded a
    permanent 88×31&nbsp;px badge of honour, pinned here for as long as
    the server remembers.
  </div>

  <div id="wall">
    <?php if (empty($entries) || empty($badgeFiles)): ?>
      <div class="empty">
        No badges have been earned yet.
        Complete the consent scroll once to claim the very first one.
      </div>
    <?php else: ?>
      <?php
        $badgeCount = count($badgeFiles);
        $i = 0;
        foreach ($entries as $entry):
          $badgePath = $badgeFiles[$i % $badgeCount];
          $i++;

          $date = isset($entry['timestamp'])
            ? $entry['timestamp']
            : (isset($entry['sessionStartIso']) ? $entry['sessionStartIso'] : 'unknown time');

          $location = isset($entry['location']) && $entry['location'] !== ''
            ? $entry['location']
            : 'location undisclosed';
      ?>
        <div class="badge">
          <span class="badge-info">
            <?php echo htmlspecialchars($date, ENT_QUOTES, 'UTF-8'); ?><br>
            <?php echo htmlspecialchars($location, ENT_QUOTES, 'UTF-8'); ?>
          </span>
          <img
            src="<?php echo htmlspecialchars(rel_url($badgePath), ENT_QUOTES, 'UTF-8'); ?>"
            alt="visitor badge"
          />
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </div>
</body>
</html>
