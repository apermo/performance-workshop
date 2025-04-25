# Performance Workshop
## by [Christoph Daum](https://christoph-daum.com)

## Setup

```ssh
./setup.sh
```

## Lessons

### Lesson 1: xhprof - limitations and toolset

In this lesson we will cover the use of xhprof, see the limitations and install and use the xhprof toolset I wrote.

We will find a query similar to this
```mysql
SELECT post_id, meta_value 
FROM wp_postmeta 
WHERE meta_key = '_wp_attached_file' AND meta_value = '2020/10/2020-08-04_103807.png';
```
It originates from `attachment_url_to_postid()` and is used to get the post id from an attachment url.
This is a very expensive query, since it will the postmeta table is not indexed for this case.

Which in my setup took 5.3s when I added this to the documentation. This query will luckily not be executed in the 
frontend, but it will slow down the saving process of a post.

### Lesson 2: Index MySQL for speed

With the data learned from xhprof, we see that there are limitations in the database queries. In this lesson we will
install and use the Plugin Index MySQL for speed.

### Lesson 3: Heartbeat

By default the heartbeat will fire once every 30 seconds for any logged in user. The heartbeat hast 2 main uses. On the 
one hand, it will ensure you will be logged out/shown the login screen if your session expired or is invalid. On the
other hand, it will ensure that a locked post, will remain locked as long as your browser is still responding.

Since this will always fire up the whole WordPress bootstrap, this will produce a certain amout of load. While this 
won't be much, adjusting this will save CPU time, resources and in the end also CO2.

In our example, we will differentiate the heartbeat between the editor, the backend and the frontend.

### Lesson 4: Media Library
`wp_enqueue_media()` will by default query all attachments to get all months with posts.
Quote: This query can be  expensive for large media libraries, so it may be desirable for sites to  override this behavior.
**Try for yourself:**
```mysql
SELECT DISTINCT YEAR( post_date ) AS year, MONTH( post_date ) AS month
FROM wp_posts
WHERE post_type = 'attachemt'
ORDER BY post_date DESC;
```

That's exactly what we're doing in this lesson. We will hook into `media_library_months_with_files` to speed up this call.

### Lesson 5: Custom Tables instead of meta query

WordPress Coding Standards have a sniff notifying that meta queries are slow and should be avoided. In the example code 
we have a slow meta query. We will use query monitor to find this and use a custom table to have the same result but 
much faster.

Prepared URLs:
https://performance.ddev.site/artikel/category/bass/ (hide from archive)
https://performance.ddev.site/artikel/category/keyboard/ (sort by rating)
https://performance.ddev.site/artikel/category/keyboard/synthesizer/ (both)

```sql
INSERT INTO wp_custom_meta (post_id, post_hide_from_archive, _user_rate_mean)
SELECT 
    p.ID AS post_id,
    COALESCE(CAST(meta1.meta_value AS UNSIGNED), 0) AS post_hide_from_archive,
    COALESCE(CAST(meta2.meta_value AS FLOAT), 0) AS _user_rate_mean
FROM 
    wp_posts p
LEFT JOIN 
    wp_postmeta meta1 
    ON p.ID = meta1.post_id 
    AND meta1.meta_key = 'post_hide_from_archive'
LEFT JOIN 
    wp_postmeta meta2 
    ON p.ID = meta2.post_id 
    AND meta2.meta_key = '_user_rate_mean'
WHERE 
    p.post_type = 'post'
    AND p.post_status = 'publish'
GROUP BY 
    p.ID;
```


### Bonus Lesson 6: PHP Sessions and Ninja Firewall

In this lesson we will learn that php sessions are blocking. This is one reason why WordPress does not use them, and
using them is generally frowned upon. The popular security plugin Ninja Firewall uses them. We will explore ways to 
improve the performance.

### Theoretical Lesson 7: Deleting unused code

While I cannot show this, in this workshop project, I have some numbers of the project that this talk was based on.
We had several dead features in our code base, by dead I mean they were there and active, but did not serve any purpose,
were not working as intended etc. 
By removing this, every single page load was sped up by 10%.

### Theoretical Lesson 8: Removing unused attachments

This was an idea we had on the way, to remove those attachments that were no longer used, find duplicates etc. While
we had a working PoC that could identify all unused attachments, since it was just about 10% of our media library we 
did not persue this any further.

### Additional Ideas

* Cleaning up your database from unused data will always be beneficial, but it comes down to the single use
  case weather it will be worth the effort
* Question your plugins. While having a lot plugins isn't automatically bad, having few isn't automatically good.
  I've seen single plugins that will cause 20-50% of load. 
* Making clever use of autoloading or conditionally loading plugins can be beneficial.
  For example, if you have a plugin that is only used in the backend, you can filter `plugins_loaded` to only load it in 
  the backend.
* Consider a custom Rest API route instead of using `admin-ajax.php`. This will save you a lot of overhead. Or even 
  bypass WordPress altogether by creating a `counter.php` file that will just access the database directly, if you want
  to count access to a post. 