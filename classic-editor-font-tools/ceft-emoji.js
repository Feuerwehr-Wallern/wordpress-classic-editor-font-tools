(function() {
    tinymce.PluginManager.add('ceft_emoji', function(editor, url) {
        editor.addButton('ceft_emoji', {
            text: '😊',
            tooltip: 'Emoji einfügen',
            onclick: function() {
                editor.windowManager.open({
                    title: 'Emoji auswählen',
                    body: [
                        {
                            type: 'listbox',
                            name: 'emoji',
                            label: 'Emoji',
                            values: [
                                 // Allgemeine Emojis
                                { text: '😄', value: '😄' },
                                { text: '❤️', value: '❤️' },
                                { text: '😂', value: '😂' },
                                { text: '🤔', value: '🤔' },
                                { text: '🥳', value: '🥳' },

                                // Feuerwehr Emojis
                                { text: '🔥', value: '🔥' },
                                { text: '🚒', value: '🚒' },
                                { text: '👨‍🚒', value: '👨‍🚒' },
                                { text: '👩‍🚒', value: '👩‍🚒' },
                                { text: '🧯', value: '🧯' },
                                { text: '🚨', value: '🚨' },
                                { text: '🪓', value: '🪓' },
                                { text: '🪖', value: '🪖' },
                                { text: '🧧', value: '🧧' },
                                { text: '💦', value: '💦' },

                                // Hände / Gesten (umfassend)
                                { text: '🙏', value: '🙏' }, // Dank
                                { text: '👍', value: '👍' }, // Daumen hoch
                                { text: '👎', value: '👎' }, // Daumen runter
                                { text: '✌️', value: '✌️' }, // Peace
                                { text: '👌', value: '👌' }, // OK
                                { text: '👊', value: '👊' }, // Fist bump
                                { text: '🤘', value: '🤘' }, // Rock
                                { text: '🤙', value: '🤙' }, // Shaka
                                { text: '☝️', value: '☝️' }, // Zeigefinger hoch
                                { text: '🤞', value: '🤞' }, // Finger kreuzen
                                { text: '🤟', value: '🤟' }, // ILY-Handzeichen
                                { text: '✋', value: '✋' }, // Hand hoch
                                { text: '🤚', value: '🤚' }, // Hand zur Seite
                                { text: '🖐️', value: '🖐️' }, // 5 Finger
                                { text: '🖖', value: '🖖' }, // Vulcan
                                { text: '✍️', value: '✍️' }, // Schreiben
                                { text: '🤲', value: '🤲' }, // Hände halten
                                { text: '👐', value: '👐' }, // Hände offen
                                { text: '🙌', value: '🙌' }, // Hände hoch Jubel
                                { text: '👏', value: '👏' }, // Klatschen
                                { text: '💪', value: '💪' }, // Muskel
                                { text: '🤝', value: '🤝' },  // Händeschütteln

                                // Deutende Hände
                                { text: '👈', value: '👈' }, // nach links zeigend
                                { text: '👉', value: '👉' }, // nach rechts zeigend
                                { text: '👆', value: '👆' }, // nach oben zeigend
                                { text: '👇', value: '👇' }, // nach unten zeigend
                                { text: '🫵', value: '🫵' }, // du / Finger zeigend
                                { text: '🫲', value: '🫲' }, // linke Hand deutend
                                { text: '🫱', value: '🫱' }  // rechte Hand deutend
                            ]
                        }
                    ],
                    onsubmit: function(e) {
                        editor.insertContent('<span class="emoji">' + e.data.emoji + '</span>');
                    }
                });
            }
        });
    });
})();
