/* global Cropper */

// パスワードの表示・非表示切り替え
function togglePassword(inputId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(inputId + "-toggle-icon");
    if (input.type === "password") {
        input.type = "text";
        icon.classList.replace("fa-eye", "fa-eye-slash");
    } else {
        input.type = "password";
        icon.classList.replace("fa-eye-slash", "fa-eye");
    }
}

// プロフィール画像の削除
function deleteAvatar() {
    if (!confirm("プロフィール画像を削除しますか？")) return;

    fetch(window.AVATAR_DELETE_URL, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
            Accept: "application/json",
        },
    })
        .then((res) => res.json())
        .then(() => {
            location.reload();
        });
}

// トリミングモーダルを開く
let cropper = null;

document.addEventListener("DOMContentLoaded", () => {
    // <input type="file" id="avatar">
    const avatarInput = document.getElementById("avatar");
    if (!avatarInput) return;

    avatarInput.addEventListener("change", () => {
        const file = avatarInput.files[0];
        if (!file) return;

        const reader = new FileReader();

        // Base64への変換が完了したときに発火
        reader.onload = () => {
            // <img id="cropImage"> に変換した画像を表示
            const cropImage = document.getElementById("cropImage");
            cropImage.src = reader.result;

            // トリミングモーダルを表示
            const cropModal = document.getElementById("cropModal");
            cropModal.style.display = "flex";

            // 以前の Cropper が残っていれば破棄
            if (cropper) cropper.destroy();
            cropper = new Cropper(cropImage, {
                aspectRatio: 1,
                viewMode: 1,
            });
        };

        // ファイルを Base64 形式に変換して読み込み開始
        reader.readAsDataURL(file);
    });
});

// トリミングモーダルを閉じる
function closeCropModal() {
    document.getElementById("cropModal").style.display = "none";
    if (cropper) {
        cropper.destroy();
        cropper = null;
    }
}

// トリミングを適用してサーバーに送信
function applyCrop() {
    // クロップ画像をCanvasで取得     // CanvasをBlobに変換
    cropper.getCroppedCanvas({ width: 300, height: 300 }).toBlob((blob) => {
        const formData = new FormData();
        // FormDataに詰める
        formData.append("avatar", blob, "avatar.jpg");

        fetch(window.AVATAR_UPLOAD_URL, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector(
                    'meta[name="csrf-token"]',
                ).content,
                Accept: "application/json",
            },
            body: formData,
        })
            .then((res) => res.json())
            .then(() => {
                location.reload();
            });
    }, "image/jpeg");
}
