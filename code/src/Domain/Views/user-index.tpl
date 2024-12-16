<p>Список пользователей в хранилище</p>
<a href="/user/edit">Добавить пользователя</a>
<ul id="navigation">
    {% for user in users %}
        <li>
            {{ user.getUserName() }} {{ user.getUserLastName() }}. День рождения: {{ user.getUserBirthday() | date('d.m.Y') }}
            
            {% if userRoles is defined and 'admin' in userRoles %}
                <a href="/user/edit/?user_id={{ user.userId }}">Править</a>
                <a href="javascript:void(0)" class="delete-user" data-user-id="{{ user.userId }}">Удалить</a>
            {% endif %}
        </li>
    {% endfor %}
</ul>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteButtons = document.querySelectorAll('.delete-user');
        deleteButtons.forEach(button => {
            button.addEventListener('click', function () {
                const userId = this.getAttribute('data-user-id');
                if (confirm('Вы уверены, что хотите удалить пользователя?')) {
                    fetch(`/user/delete-ajax`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token }}'
                        },
                        body: JSON.stringify({ user_id: userId })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Пользователь удален успешно');
                            location.reload();
                        } else {
                            alert('Ошибка удаления пользователя: ' + data.error);
                        }
                    })
                    .catch(error => console.error('Ошибка:', error));
                }
            });
        });
    });
</script>
