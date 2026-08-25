<?php

function serialize_announcement(array $a): array
{
    return [
        'id'         => (int) $a['id'],
        'title'      => (string) $a['title'],
        'message'    => (string) $a['message'],
        'category'   => (string) ($a['category'] ?? 'General'),
        'audience'   => (string) ($a['audience'] ?? 'General'),
        'created_at' => $a['created_at'] ?? null,
    ];
}

function serialize_post(array $p): array
{
    $file = trim((string) ($p['file_path'] ?? ''));

    return [
        'id'         => (int) $p['id'],
        'title'      => (string) $p['title'],
        'content'    => (string) $p['content'],
        'author'     => (string) ($p['author'] ?? 'Admin'),
        'has_file'   => $file !== '',
        'file_url'   => $file !== ''
            ? api_route_url('posts/' . (int) $p['id'] . '/file')
            : null,
        'created_at' => $p['created_at'] ?? null,
    ];
}

function resolve_passer_photo(?string $photo): ?string
{
    $photo = basename(trim((string) $photo));

    if ($photo === '' || $photo === 'default_user.jpg') {
        return null;
    }

    if (is_file(api_upload_dir('passers') . DIRECTORY_SEPARATOR . $photo)) {
        return uploads_url('passers/' . $photo);
    }

    if (is_file(api_upload_dir('profiles') . DIRECTORY_SEPARATOR . $photo)) {
        return uploads_url('profiles/' . $photo);
    }

    return null;
}

function serialize_testimonial(array $t): array
{
    return [
        'id'         => (int) $t['id'],
        'content'    => (string) $t['content'],
        'created_at' => $t['created_at'] ?? null,
        'student'    => [
            'name'            => trim(($t['firstname'] ?? '') . ' ' . ($t['lastname'] ?? '')),
            'profile_pic'     => !empty($t['profile_pic']) ? $t['profile_pic'] : null,
            'profile_pic_url' => !empty($t['profile_pic']) ? uploads_url('profiles/' . $t['profile_pic']) : null,
        ],
    ];
}

function posts_list(mysqli $conn, array $params, ?array $ctx): void
{
    $result = mysqli_query($conn, "SELECT * FROM posts ORDER BY created_at DESC, id DESC");

    $posts = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $posts[] = serialize_post($row);
    }

    api_ok(['posts' => $posts]);
}

function post_file(mysqli $conn, array $params, ?array $ctx): void
{
    $post_id = (int) ($params[1] ?? 0);

    $stmt = $conn->prepare("SELECT file_path FROM posts WHERE id = ? LIMIT 1");
    $stmt->bind_param('i', $post_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    $file = trim((string) ($row['file_path'] ?? ''));

    if (!$row || $file === '') {
        api_fail(404, 'not_found', 'Resource file not found.');
    }

    api_stream_upload_file('resources', $file);
}

function announcements_list(mysqli $conn, array $params, ?array $ctx): void
{
    $page = query_int('page', 1, 1, 10000);
    $per_page = query_int('per_page', 10, 1, 50);
    $offset = ($page - 1) * $per_page;

    $total = (int) mysqli_fetch_assoc(
        mysqli_query($conn, "SELECT COUNT(*) AS c FROM announcements WHERE audience = 'Students'")
    )['c'];

    $stmt = $conn->prepare(
        "SELECT * FROM announcements
         WHERE audience = 'Students'
         ORDER BY created_at DESC, id DESC
         LIMIT ? OFFSET ?"
    );
    $stmt->bind_param('ii', $per_page, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    $announcements = [];
    while ($row = $result->fetch_assoc()) {
        $announcements[] = serialize_announcement($row);
    }
    $stmt->close();

    api_ok([
        'announcements' => $announcements,
        'meta' => [
            'page'        => $page,
            'per_page'    => $per_page,
            'total'       => $total,
            'total_pages' => (int) ceil($total / $per_page),
        ],
    ]);
}

function passers_list(mysqli $conn, array $params, ?array $ctx): void
{
    $result = mysqli_query($conn, "SELECT * FROM passers ORDER BY id DESC");

    $passers = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rating = $row['rating'] !== null ? (float) $row['rating'] : null;

        $passers[] = [
            'id'          => (int) $row['id'],
            'name'        => (string) $row['name'],
            'program'     => (string) $row['program'],
            'batch'       => (string) $row['batch'],
            'rating'      => $rating,
            'top_passer'  => $rating !== null && $rating >= 95,
            'exam_date'   => $row['exam_date'] ?? null,
            'photo_url'   => resolve_passer_photo($row['photo'] ?? null),
            'created_at'  => $row['created_at'] ?? null,
        ];
    }

    api_ok(['passers' => $passers]);
}

function gallery_list(mysqli $conn, array $params, ?array $ctx): void
{
    $result = mysqli_query(
        $conn,
        "SELECT * FROM gallery_images ORDER BY caption ASC, sort_order ASC, id ASC"
    );

    $groups = [];

    while ($row = mysqli_fetch_assoc($result)) {
        $caption = (string) $row['caption'];

        if (!isset($groups[$caption])) {
            $groups[$caption] = [
                'caption' => $caption,
                'images'  => [],
            ];
        }

        $groups[$caption]['images'][] = [
            'id'         => (int) $row['id'],
            'image_url'  => uploads_url('gallery/' . $row['image_path']),
            'sort_order' => (int) $row['sort_order'],
            'created_at' => $row['created_at'] ?? null,
        ];
    }

    api_ok(['groups' => array_values($groups)]);
}

function testimonials_list(mysqli $conn, array $params, ?array $ctx): void
{
    $limit = query_int('limit', 20, 1, 100);

    $stmt = $conn->prepare(
        "SELECT t.*, u.firstname, u.lastname, u.profile_pic
         FROM testimonials t
         JOIN users u ON t.user_id = u.id
         ORDER BY t.created_at DESC, t.id DESC
         LIMIT ?"
    );
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();

    $testimonials = [];
    while ($row = $result->fetch_assoc()) {
        $testimonials[] = serialize_testimonial($row);
    }
    $stmt->close();

    api_ok(['testimonials' => $testimonials]);
}

function testimonial_create(mysqli $conn, array $params, ?array $ctx): void
{
    $in = json_body();

    $errors = validate_fields($in, [
        'content' => ['required', 'max:2000'],
    ]);

    if (!empty($errors)) {
        api_fail(422, 'validation_error', 'Please check the highlighted fields.', $errors);
    }

    $user_id = (int) $ctx['user']['id'];
    $content = input_str($in, 'content');

    $stmt = $conn->prepare("INSERT INTO testimonials (user_id, content) VALUES (?, ?)");

    if (!$stmt) {
        error_log('API testimonial prepare failed: ' . $conn->error);
        api_fail(500, 'server_error', 'Could not submit testimonial.');
    }

    $stmt->bind_param('is', $user_id, $content);

    if (!$stmt->execute()) {
        error_log('API testimonial insert failed: ' . $stmt->error);
        api_fail(500, 'server_error', 'Could not submit testimonial.');
    }

    $testimonial_id = $stmt->insert_id;
    $stmt->close();

    log_activity($conn, 'testimonial.submit', null, [
        'user_id'     => $user_id,
        'user_role'   => 'student',
        'entity_type' => 'testimonial',
        'entity_id'   => $testimonial_id,
    ]);

    api_ok(['message' => 'Thank you for your testimonial!'], 201);
}
