<?php
/*
Plugin Name: Esefoke Audio
Plugin URI: https://esefoke.xyz
Description: Convert WordPress posts into natural audio narration using Microsoft Edge-TTS.
Version: 1.5.0
Author: Frank Okenegede
Author URI: https://esefoke.xyz
License: GPL v2 or later
Text Domain: esefoke-audio
*/

define('ESEFOKE_AUDIO_VERSION', '1.5.0');

if(!defined('ABSPATH')) {
    exit;
}

function esefoke_audio_admin_menu() {

    add_menu_page(
        'Esefoke Audio',
        'Esefoke Audio',
        'manage_options',
        'esefoke-audio',
        'esefoke_audio_dashboard_page',
        'dashicons-format-audio',
        25
    );

    add_submenu_page(
        'esefoke-audio',
        'Dashboard',
        'Dashboard',
        'manage_options',
        'esefoke-audio',
        'esefoke_audio_dashboard_page'
    );

    add_submenu_page(
        'esefoke-audio',
        'Generate Audio',
        'Generate Audio',
        'manage_options',
        'esefoke-audio-generate',
        'esefoke_audio_generate_page'
    );

    add_submenu_page(
        'esefoke-audio',
        'Audio Library',
        'Audio Library',
        'manage_options',
        'esefoke-audio-library',
        'esefoke_audio_library_page'
    );

    add_submenu_page(
        'esefoke-audio',
        'Settings',
        'Settings',
        'manage_options',
        'esefoke-audio-settings',
        'esefoke_audio_settings_page'
    );
}

function esefoke_audio_dashboard_page() {

$post_counts = wp_count_posts('post');

$total_posts = isset($post_counts->publish)
    ? $post_counts->publish
    : 0;

$audio_posts = 0;

$posts = get_posts(array(
    'post_type' => 'post',
    'post_status' => 'publish',
    'numberposts' => -1
));

$posts = get_posts(array(
    'post_type' => 'post',
    'posts_per_page' => -1
));

foreach($posts as $post) {

    $audio = get_post_meta(
        $post->ID,
        '_esefoke_audio_file',
        true
    );

    if(!empty($audio)) {
        $audio_posts++;
    }
}

$pending_posts = $total_posts - $audio_posts;

    echo '<div class="wrap">';

    echo '<h1>Esefoke Audio Dashboard</h1>';

    echo '<div style="display:flex;gap:20px;margin-top:20px;">';

echo '<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:10px;width:180px;">';
echo '<h2>' . $total_posts . '</h2>';
echo '<p>Total Posts</p>';
echo '</div>';

echo '<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:10px;width:180px;">';
echo '<h2>' . $audio_posts . '</h2>';
echo '<p>Audio Generated</p>';
echo '</div>';

echo '<div style="background:#fff;padding:20px;border:1px solid #ddd;border-radius:10px;width:180px;">';
echo '<h2>' . $pending_posts . '</h2>';
echo '<p>Pending Audio</p>';
echo '</div>';

echo '</div>';

    echo '</div>';

}

function esefoke_generate_audio($post_id) {

    $post = get_post($post_id);

    if(!$post) {
        return false;
    }

    $title = $post->post_title;

    $content = wp_strip_all_tags(
        $post->post_content
    );

    $provider = get_option(
        'esefoke_audio_provider',
        'edge_tts'
    );

    $voice = get_option(
        'esefoke_audio_voice',
        'alloy'
    );

    $speed = get_option(
        'esefoke_audio_speed',
        '1.0'
    );

    $text = $title . "\n\n" . $content;

$text = str_replace(
    array(
        '“',
        '”',
        '‘',
        '’',
        '—',
        '–',
        '🎧',
        '⬇'
    ),
    array(
        '"',
        '"',
        "'",
        "'",
        '-',
        '-',
        '',
        ''
    ),
    $text
);

$text = preg_replace('/[^\P{C}\n]+/u', '', $text);

    $upload_dir = wp_upload_dir();

    $audio_folder =
        $upload_dir['basedir']
        . '/esefoke-audio';

    if(!file_exists($audio_folder)) {

        mkdir($audio_folder, 0755, true);

    }

    $filename =
        sanitize_title($title)
        . '.txt';

    $filepath =
        $audio_folder
        . '/'
        . $filename;

    file_put_contents(
        $filepath,
        $text
    );

    $mp3_filename =
        sanitize_title($title)
        . '.mp3';

    $mp3_filepath =
        $audio_folder
        . '/'
        . $mp3_filename;

   $edge_tts = get_option(
    'esefoke_audio_path',
    ''
);

if(empty($edge_tts)) {
    return false;
}

    $selected_voice = get_option(
        'esefoke_audio_voice',
        'jenny'
    );

    $voice_map = array(
        'jenny' => 'en-US-JennyNeural',
        'guy'   => 'en-US-GuyNeural',
        'sonia' => 'en-GB-SoniaNeural',
        'ryan'  => 'en-GB-RyanNeural'
    );

    $edge_voice =
        isset($voice_map[$selected_voice])
        ? $voice_map[$selected_voice]
        : 'en-US-JennyNeural';

    $rate_percent = intval(
        (floatval($speed) - 1) * 100
    );

    if($rate_percent >= 0) {
        $edge_rate =
            '+' . $rate_percent . '%';
    } else {
        $edge_rate =
            $rate_percent . '%';
    }

    $command =
        '"' . $edge_tts . '" --file '
        . escapeshellarg($filepath)
        . ' --voice '
        . escapeshellarg($edge_voice)
        . ' --rate='
        . $edge_rate
        . ' --write-media '
        . escapeshellarg($mp3_filepath);

    exec(
        $command,
        $output,
        $return_var
    );

    if($return_var !== 0) {
        return false;
    }

    update_post_meta(
        $post_id,
        '_esefoke_audio_file',
        $mp3_filename
    );

    if($provider == 'edge_tts') {
    $provider = 'Edge-TTS';
}

    update_post_meta(
        $post_id,
        '_esefoke_audio_provider',
        $provider
    );

    update_post_meta(
        $post_id,
        '_esefoke_audio_voice',
        $voice
    );

    update_post_meta(
        $post_id,
        '_esefoke_audio_speed',
        $speed
    );

    update_post_meta(
        $post_id,
        '_esefoke_audio_created',
        current_time('mysql')
    );

    return true;
}

function esefoke_audio_generate_page() {

    $posts = get_posts(array(
        'post_type' => 'post',
        'numberposts' => 10
    ));

    ?>

    <div class="wrap">

        <h1>Generate Audio</h1>

        <p>Select a post to generate audio narration.</p>

        <?php

if(isset($_POST['convert_audio'])) {

  $post_id = intval($_POST['post_id']);

$result = esefoke_generate_audio($post_id);

if(!$result) {

    echo '<div class="notice notice-error">';
    echo '<p>Audio generation failed.</p>';
    echo '</div>';

} else {

    $audio_file = get_post_meta(
        $post_id,
        '_esefoke_audio_file',
        true
    );

    $provider = get_post_meta(
        $post_id,
        '_esefoke_audio_provider',
        true
    );

    $voice = get_post_meta(
        $post_id,
        '_esefoke_audio_voice',
        true
    );

    $speed = get_post_meta(
        $post_id,
        '_esefoke_audio_speed',
        true
    );

    echo '<div class="notice notice-success">';

    echo '<p>Audio generated successfully!</p>';

    echo '<p><strong>Audio File:</strong> '
        . esc_html($audio_file)
        . '</p>';

    echo '<p><strong>Provider:</strong> '
        . esc_html($provider)
        . '</p>';

    echo '<p><strong>Voice:</strong> '
        . esc_html($voice)
        . '</p>';

    echo '<p><strong>Speed:</strong> '
        . esc_html($speed)
        . '</p>';

    echo '</div>';
}

}

if(isset($_POST['generate_missing_audio'])) {

    $posts = get_posts(array(
        'post_type'   => 'post',
        'post_status' => 'publish',
        'numberposts' => -1
    ));

    $generated = 0;

    foreach($posts as $post) {

        $audio = get_post_meta(
            $post->ID,
            '_esefoke_audio_file',
            true
        );

        if(!empty($audio)) {
            continue;
        }

        $result = esefoke_generate_audio(
            $post->ID
        );

        if($result) {
            $generated++;
        }
    }

    echo '<div class="notice notice-success">';

    echo '<p>';

    echo $generated .
        ' audio file(s) generated successfully.';

    echo '</p>';

    echo '</div>';
}
?>

        <?php

        if(isset($_GET['delete_file'])) {

    $audio_file = sanitize_file_name(
        $_GET['delete_file']
    );

    $upload_dir = wp_upload_dir();

    $mp3_file =
        $upload_dir['basedir']
        . '/esefoke-audio/'
        . $audio_file;

    $txt_file =
        $upload_dir['basedir']
        . '/esefoke-audio/'
        . str_replace('.mp3', '.txt', $audio_file);

    if(file_exists($mp3_file)) {
        unlink($mp3_file);
    }

    if(file_exists($txt_file)) {
        unlink($txt_file);
    }

    // Remove metadata from the matching post.
    $posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1
    ));

    foreach($posts as $post) {

        $saved_file = get_post_meta(
            $post->ID,
            '_esefoke_audio_file',
            true
        );

        if($saved_file == $audio_file) {

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_file'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_provider'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_voice'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_speed'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_created'
            );

            break;
        }
    }

    echo '<div class="notice notice-success">';
    echo '<p>Audio deleted successfully.</p>';
    echo '</div>';
}

if(isset($_GET['delete_audio'])) {

    $post_id = intval($_GET['delete_audio']);

    $audio_file = get_post_meta(
        $post_id,
        '_esefoke_audio_file',
        true
    );

    if(!empty($audio_file)) {

        $upload_dir = wp_upload_dir();

        $mp3_file =
            $upload_dir['basedir']
            . '/esefoke-audio/'
            . $audio_file;

        $txt_file =
            $upload_dir['basedir']
            . '/esefoke-audio/'
            . str_replace('.mp3', '.txt', $audio_file);

        if(file_exists($mp3_file)) {
            unlink($mp3_file);
        }

        if(file_exists($txt_file)) {
            unlink($txt_file);
        }

        delete_post_meta(
            $post_id,
            '_esefoke_audio_file'
        );

        delete_post_meta(
            $post_id,
            '_esefoke_audio_provider'
        );

        delete_post_meta(
            $post_id,
            '_esefoke_audio_voice'
        );

        delete_post_meta(
            $post_id,
            '_esefoke_audio_speed'
        );

        delete_post_meta(
            $post_id,
            '_esefoke_audio_created'
        );

        echo '<div class="notice notice-success">';
        echo '<p>Audio deleted successfully.</p>';
        echo '</div>';
    }
}

if(isset($_GET['generate_audio'])) {

    $post_id = intval($_GET['generate_audio']);

    $selected_post = get_post($post_id);

    ?>

    <div style="background:#fff; padding:20px; border-left:4px solid #2271b1; margin-bottom:20px;">

        <h2>Audio Generation Started</h2>

        <p>

            <strong>Post:</strong>

            <?php echo $selected_post->post_title; ?>

        </p>

        <textarea
            style="width:100%; height:250px; padding:10px; font-size:15px;"
        ><?php echo wp_strip_all_tags($selected_post->post_content); ?></textarea>

        <br><br>

        <form method="POST">

            <input
                type="hidden"
                name="post_id"
                value="<?php echo $selected_post->ID; ?>"
            >

            <button
                type="submit"
                name="convert_audio"
                class="button button-primary button-large"
            >
                Convert To Audio
            </button>

        </form>
        
    </div>

    <?php

}

?>

<hr>

<h2>Generate Missing Audio</h2>

<form method="post">

    <p>
        Generate audio for all posts that do not yet have audio.
    </p>

    <input
        type="submit"
        name="generate_missing_audio"
        class="button button-primary"
        value="Generate Missing Audio"
    >

</form>

<br>

<?php

$posts = get_posts(array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'post_status'    => 'publish'
));

?>

<table class="widefat striped">

            <thead>

              <tr>
    <th>Post Title</th>
    <th>Status</th>
    <th>Action</th>
</tr>

            </thead>

            <tbody>

            <?php foreach($posts as $post) : ?>

               <tr>

    <td>

        <?php echo $post->post_title; ?>

    </td>

    <td>

  <?php

$audio_file = get_post_meta(
    $post->ID,
    '_esefoke_audio_file',
    true
);

if(!empty($audio_file)) {

    echo '✅ Generated';

    $provider = get_post_meta(
        $post->ID,
        '_esefoke_audio_provider',
        true
    );

    $voice = get_post_meta(
        $post->ID,
        '_esefoke_audio_voice',
        true
    );

    $created = get_post_meta(
        $post->ID,
        '_esefoke_audio_created',
        true
    );

echo '<br><small style="color:#666;">';
echo 'File: ' . esc_html($audio_file);
echo '<br>';
echo 'Provider: ' . esc_html($provider);
echo ' | ';
echo 'Voice: ' . esc_html($voice);
echo ' | ';
echo 'Created: ' . esc_html($created);
echo '</small>';

} else {

    echo '❌ No Audio';

}
?>

    </td>

   <td>

    <a href="?page=esefoke-audio-generate&generate_audio=<?php echo $post->ID; ?>">

        <button class="button button-primary">

            <?php
            if(!empty($audio_file)) {
                echo 'Regenerate Audio';
            } else {
                echo 'Generate Audio';
            }
            ?>

        </button>

    </a>

    <?php if(!empty($audio_file)) : ?>

    <a
        href="?page=esefoke-audio-generate&delete_audio=<?php echo $post->ID; ?>"
        onclick="return confirm('Delete this audio file?');"
    >

        <button class="button">

            Delete Audio

        </button>

    </a>

<?php endif; ?>

</td>

</tr>

            <?php endforeach; ?>

            </tbody>

        </table>

    </div>

    <?php
}

function esefoke_audio_library_page() {

    ?>

    <?php

if(isset($_GET['delete_file'])) {

    $upload_dir = wp_upload_dir();

    $audio_folder =
        $upload_dir['basedir'] . '/esefoke-audio';

    $file = basename($_GET['delete_file']);

    $filepath = $audio_folder . '/' . $file;

    $txtfile =
        $audio_folder . '/' .
        str_replace('.mp3', '.txt', $file);

    if(file_exists($filepath)) {
        unlink($filepath);
    }

    if(file_exists($txtfile)) {
        unlink($txtfile);
    }

    $posts = get_posts(array(
        'post_type' => 'post',
        'posts_per_page' => -1
    ));

    foreach($posts as $post) {

        $saved_file = get_post_meta(
            $post->ID,
            '_esefoke_audio_file',
            true
        );

        if($saved_file == $file) {

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_file'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_provider'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_voice'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_speed'
            );

            delete_post_meta(
                $post->ID,
                '_esefoke_audio_created'
            );

            break;
        }
    }

    echo '<div class="notice notice-success">';
    echo '<p>Audio deleted successfully.</p>';
    echo '</div>';
}

?>

     <div class="wrap">

        <h1>Audio Library</h1>

        <p>Generated audio files will appear here.</p>

        <h2>Generated Files</h2>

        <table class="widefat striped">

            <thead>

                <tr>

                    <th>File Name</th>

                    <th>Size</th>

                    <th>Date Created</th>

                    <th>Action</th>

                </tr>

            </thead>

            <tbody>

            <?php

            $upload_dir = wp_upload_dir();

            $audio_folder = $upload_dir['basedir'] . '/esefoke-audio';

            if(file_exists($audio_folder)) {

                $files = scandir($audio_folder);

                foreach($files as $file) {

                if(pathinfo($file, PATHINFO_EXTENSION) != 'mp3') {
    continue;
}

                    if($file == '.' || $file == '..') {
                        continue;
                    }

                    $fileurl = $upload_dir['baseurl'] . '/esefoke-audio/' . $file;

                    $filepath = $audio_folder . '/' . $file;

                    $filesize = round(
                        filesize($filepath) / 1024,
                        2
                    );

                    $filedate = date(
                        'd-M-Y H:i',
                        filemtime($filepath)
                    );

                    ?>

                    <tr>

                        <td><?php echo $file; ?></td>

                        <td><?php echo $filesize; ?> KB</td>

                        <td><?php echo $filedate; ?></td>

                        <td>

                            <?php if(pathinfo($file, PATHINFO_EXTENSION) == 'mp3') : ?>

                                <audio controls style="max-width:250px;">

                                    <source
                                        src="<?php echo $fileurl; ?>"
                                        type="audio/mpeg"
                                    >

                                </audio>

                                <br><br>

                            <?php endif; ?>

                            <a
                                href="<?php echo $fileurl; ?>"
                                target="_blank"
                                class="button button-secondary"
                            >
                                Download
                            </a>

                            <a
                                href="?page=esefoke-audio-library&delete_file=<?php echo urlencode($file); ?>"
                                class="button button-link-delete"
                                onclick="return confirm('Delete this file?');"
                            >
                                Delete
                            </a>

                        </td>

                    </tr>

                    <?php
                }
            }

            ?>

            </tbody>

        </table>

    </div>

    <?php
}
add_action('admin_menu', 'esefoke_audio_admin_menu');

function esefoke_audio_register_settings() {

    register_setting(
        'esefoke_audio_settings_group',
        'esefoke_audio_voice'
    );

    register_setting(
        'esefoke_audio_settings_group',
        'esefoke_audio_speed'
    );

    register_setting(
    'esefoke_audio_settings_group',
    'esefoke_audio_provider'
);

register_setting(
    'esefoke_audio_settings_group',
    'esefoke_audio_path'
);

    register_setting(
        'esefoke_audio_settings_group',
        'esefoke_audio_auto_generate'
    );

}

add_action(
    'admin_init',
    'esefoke_audio_register_settings'
);

function esefoke_audio_settings_page() {

    $posts = get_posts(array(
        'post_type' => 'post',
        'numberposts' => 10
    ));

    ?>

    <div class="wrap">
        <h1>Esefoke Audio Plugin</h1>

        <h2>Settings</h2>

<form method="post" action="options.php">

    <?php

    settings_fields(
        'esefoke_audio_settings_group'
    );

    ?>

   <table class="form-table">

    <tr>

        <th>Provider</th>
    <td>

        <?php

        $provider = get_option(
    'esefoke_audio_provider',
    'edge_tts'
);

        ?>

        <select name="esefoke_audio_provider">

            <option value="edge_tts" <?php selected($provider, 'edge_tts'); ?>>
    Edge-TTS
</option>

        </select>

    </td>

</tr>

<tr>

    <th>Edge-TTS Path</th>

    <td>

        <?php

        $edge_path = get_option(
            'esefoke_audio_path',
            ''
        );

        ?>

        <input
            type="text"
            name="esefoke_audio_path"
            value="<?php echo esc_attr($edge_path); ?>"
            style="width:500px;"
        >

        <p class="description">
            Full path to edge-tts.exe
        </p>

    </td>

</tr>

<tr>

    <th>Auto Generate Audio</th>

    <td>

        <?php
        $auto_generate = get_option(
            'esefoke_audio_auto_generate',
            'off'
        );
        ?>

        <select name="esefoke_audio_auto_generate">

            <option value="off" <?php selected($auto_generate, 'off'); ?>>
                Disabled
            </option>

            <option value="on" <?php selected($auto_generate, 'on'); ?>>
                Enabled
            </option>

        </select>

    </td>

</tr>

<tr>

    <th>Voice</th>

    <td>

        <select name="esefoke_audio_voice">

            <?php

            $provider = get_option(
    'esefoke_audio_provider',
    'edge_tts'
);

  $voice = get_option(
    'esefoke_audio_voice',
    'jenny'
);

            ?>

<option value="jenny" <?php selected($voice, 'jenny'); ?>>
    Jenny (US Female)
</option>

<option value="guy" <?php selected($voice, 'guy'); ?>>
    Guy (US Male)
</option>

<option value="sonia" <?php selected($voice, 'sonia'); ?>>
    Sonia (UK Female)
</option>

<option value="ryan" <?php selected($voice, 'ryan'); ?>>
    Ryan (UK Male)
</option>

        </select>

    </td>

</tr>

<tr>

    <th>Speech Speed</th>

    <td>

        <input
            type="number"
            step="0.1"
            min="0.5"
            max="2.0"
            name="esefoke_audio_speed"
            value="<?php echo esc_attr(get_option('esefoke_audio_speed', '1.0')); ?>"
        >

        <p class="description">
            1.0 = Normal Speed
        </p>

    </td>

</tr>

    </table>

    <?php submit_button('Save Settings'); ?>

</form>

<hr>


        <?php

if(isset($_GET['delete_file'])) {

    $upload_dir = wp_upload_dir();

    $audio_folder = $upload_dir['basedir'] . '/esefoke-audio';

    $file = basename($_GET['delete_file']);

    $filepath = $audio_folder . '/' . $file;

    if(file_exists($filepath)) {

        unlink($filepath);

        echo '<div class="notice notice-success">';
        echo '<p>File deleted successfully!</p>';
        echo '</div>';
    }
}
?>

        <?php

if(isset($_POST['convert_audio'])) {

    echo "<div style='padding:15px; background:#fff; border-left:4px solid blue; margin-bottom:20px;'>";

    echo "<h3>Audio Generation Started</h3>";

    echo "<p>Your narration is being prepared...</p>";

    echo "</div>";
}

if(isset($_GET['generate_audio'])) {

    $post_id = intval($_GET['generate_audio']);

    $selected_post = get_post($post_id);

    ?>

    <div style="background:#fff; padding:20px; border-left:4px solid #2271b1; margin-bottom:20px;">

        <h2>Audio Generation Started</h2>

        <p>
            <strong>Post:</strong>
            <?php echo $selected_post->post_title; ?>
        </p>

        <textarea
            style="width:100%; height:250px; padding:10px; font-size:15px;"
        ><?php echo wp_strip_all_tags($selected_post->post_content); ?></textarea>

       <br><br>

<form method="POST">

    <input
        type="hidden"
        name="post_id"
        value="<?php echo $selected_post->ID; ?>"
    >

    <button
        type="submit"
        name="convert_audio"
        class="button button-primary button-large"
    >
        Convert To Audio
    </button>

</form>

    </div>

    <?php
}
?>

    </div>

<?php
}

function esefoke_audio_shortcode() {

    global $post;

    if(!$post) {
        return '';
    }

  $filename = get_post_meta(
    $post->ID,
    '_esefoke_audio_file',
    true
);

if(empty($filename)) {
    return '';
}

$upload_dir = wp_upload_dir();

$audio_url =
    $upload_dir['baseurl']
    . '/esefoke-audio/'
    . $filename;

ob_start();
?>

<div class="esefoke-audio-player">

    <h3>🎧 Listen to this article</h3>

    <audio controls preload="metadata">

        <source
            src="<?php echo esc_url($audio_url); ?>"
            type="audio/mpeg">

        Your browser does not support audio playback.

   </audio>

<p style="margin-top:10px;">

    <a
        href="<?php echo esc_url($audio_url); ?>"
        download
        class="button">

        ⬇ Download MP3

    </a>

</p>

</div>

<?php

return ob_get_clean();
}

add_shortcode(
    'esefoke_audio',
    'esefoke_audio_shortcode'
);

function esefoke_audio_auto_insert($content) {

    if(!is_single()) {
        return $content;
    }

    global $post;

    if(!$post) {
        return $content;
    }

    $audio_file = get_post_meta(
        $post->ID,
        '_esefoke_audio_file',
        true
    );

    if(empty($audio_file)) {
        return $content;
    }

    return do_shortcode('[esefoke_audio]') . $content;
}

add_filter(
    'the_content',
    'esefoke_audio_auto_insert'
);

function esefoke_audio_inline_styles() {
    ?>
    <style>

    .esefoke-audio-player{
        background:#f8f9fa;
        border:1px solid #ddd;
        border-radius:12px;
        padding:20px;
        margin:20px 0;
        box-shadow:0 2px 8px rgba(0,0,0,0.05);
    }

    .esefoke-audio-player h3{
        margin-top:0;
        margin-bottom:15px;
        font-size:18px;
    }

    .esefoke-audio-player audio{
        width:100%;
    }

    </style>
    <?php
}

add_action(
    'publish_post',
    'esefoke_audio_auto_generate'
);

function esefoke_audio_auto_generate($post_id) {

    $auto_generate = get_option(
        'esefoke_audio_auto_generate',
        'off'
    );

    if($auto_generate != 'on') {
        return;
    }

    $existing_audio = get_post_meta(
        $post_id,
        '_esefoke_audio_file',
        true
    );

    if(!empty($existing_audio)) {
        return;
    }

    esefoke_generate_audio($post_id);
}

add_action(
    'wp_head',
    'esefoke_audio_inline_styles'
);