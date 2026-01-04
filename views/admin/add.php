<section>
    <h2>Add new entry</h2>
    <form method="post">
        <input type="hidden" name="action" value="add">
        <input type="hidden" name="view" value="add">
        <p>Question:<br>
            <textarea name="question" rows="3" cols="80"></textarea>
        </p>
        <p>Answer (Markdown):<br>
            <textarea name="answer" rows="5" cols="80"></textarea>
        </p>
        <p>Tags (comma-separated):<br>
            <input type="text" name="tags" value="<?php echo $e($addTagsInput ?? ''); ?>" size="80" placeholder="faith, history, ethics">
        </p>
        <?php if (!empty($allTags)): ?>
            <div class="tag-picker">
                <label>Find tags:
                    <input type="search" class="tag-search" placeholder="Search tags">
                </label>
                <div class="tag-list">
                    <?php foreach ($allTags as $tag): ?>
                        <button type="button" class="tag-chip" data-tag="<?php echo $e($tag); ?>"><?php echo $e($tag); ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>
        <button type="submit">Save</button>
    </form>
</section>
<script>
    (function () {
        var pickers = document.querySelectorAll('.tag-picker');
        if (!pickers.length) {
            return;
        }

        function parseTags(value) {
            return String(value || '')
                .split(/[,\\n]+/)
                .map(function (tag) { return tag.trim(); })
                .filter(function (tag) { return tag.length > 0; });
        }

        function tagSet(value) {
            var set = {};
            parseTags(value).forEach(function (tag) {
                set[tag.toLowerCase()] = true;
            });
            return set;
        }

        function addTag(value, tag) {
            var tags = parseTags(value);
            var key = tag.toLowerCase();
            var set = tagSet(value);
            if (!set[key]) {
                tags.push(tag);
            }
            return tags.join(', ');
        }

        pickers.forEach(function (picker) {
            var input = picker.closest('form').querySelector('input[name="tags"]');
            var search = picker.querySelector('.tag-search');
            var chips = Array.prototype.slice.call(picker.querySelectorAll('.tag-chip'));
            if (!input || !search || chips.length === 0) {
                return;
            }

            function updateSelected() {
                var set = tagSet(input.value);
                chips.forEach(function (chip) {
                    var text = chip.getAttribute('data-tag') || chip.textContent || '';
                    var selected = !!set[String(text).toLowerCase()];
                    chip.classList.toggle('is-selected', selected);
                });
            }

            function filterTags() {
                var query = String(search.value || '').toLowerCase();
                chips.forEach(function (chip) {
                    var text = chip.getAttribute('data-tag') || chip.textContent || '';
                    var match = text.toLowerCase().indexOf(query) !== -1;
                    chip.style.display = match ? '' : 'none';
                });
            }

            chips.forEach(function (chip) {
                chip.addEventListener('click', function () {
                    var tag = chip.getAttribute('data-tag') || chip.textContent || '';
                    if (tag === '') {
                        return;
                    }
                    input.value = addTag(input.value, tag);
                    updateSelected();
                    input.focus();
                });
            });

            search.addEventListener('input', filterTags);
            input.addEventListener('input', updateSelected);
            updateSelected();
        });
    })();
</script>
