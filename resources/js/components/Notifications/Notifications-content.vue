<template>
    <div class="notifications-container">
        <div class="black-line"></div>
        <div class="top-bar">
            <div class="top-top">
                <button class="back-icon" @click="goBack">
                    <ion-icon name="arrow-back-outline"></ion-icon>
                </button>
                <div class="top-top-top" v-if="user">
                    <p class="title">Notifications</p>
                    <p class="user-tag">{{ user.UserTag }}</p>
                </div>
            </div>
            <div class="noti-type">
                <button @click="switchToAll" class="noti-type-btn" :class ="{ 'active-noti-type': notiType == 'all' }">
                    <div class="noti-title">
                        All
                        <div class="noti-count" v-if="all_count > 0">{{ all_count }}</div>
                    </div>
                    <div class="active-line" :class ="{ 'active': notiType == 'all' }"></div>
                </button>
                <button @click="switchToVerified" class="noti-type-btn" :class ="{ 'active-noti-type': notiType == 'verified' }">
                    <div class="noti-title">
                        Verified
                        <div class="noti-count" v-if="verified_count > 0">{{ verified_count }}</div>
                    </div>
                    <div class="active-line" :class ="{ 'active': notiType == 'verified' }"></div>
                </button>
                <button @click="switchToMentions" class="noti-type-btn" :class ="{ 'active-noti-type': notiType == 'mentions' }">
                    <div class="noti-title">
                        Mentions
                        <div class="noti-count" v-if="mentions_count > 0">{{ mentions_count }}</div>
                    </div>
                    <div class="active-line" :class ="{ 'active': notiType == 'mentions' }"></div>
                </button>
            </div>
            <div class="notification-buttons">
                <input type="checkbox" @click.stop="selectAll()" id="selectAllCheckBox" title="Select all">
                <button @click="selectUnread" class="noti-btn" title="Select unread"><ion-icon name="mail-unread"></ion-icon></button>
                <button @click="selectRead" class="noti-btn" title="Select read"><ion-icon name="mail-open"></ion-icon></button>
                <button @click="markSelectedAsRead" class="noti-btn" title="Mark selected as read"><ion-icon name="book"></ion-icon></button>
                <button @click="deleteSelected" class="noti-btn" title="Delete selected"><ion-icon class="delete" name="trash"></ion-icon></button>
            </div>
        </div>

        <div class="noti-main" v-if ="currentNotifications.length <= 0">
            <div class="main-text">Nothing to see here — yet</div>
            <div class="under-text" v-if="notiType=='mentions'">When someone mentions you, you'll find it here.</div>
            <div class="under-text" v-if="notiType=='verified'">Likes, mentions, reposts, and a whole lot more — when it comes from a verified account, you’ll find it here</div>
            <div class="under-text" v-if="notiType=='all'">All notifications will be shown here.</div>
        </div>
        <div class="notification-container">
            <div class="notification" v-for="notification in currentNotifications" :key="notification.NotificationID" @click="openLink(notification.NotificationLink, notification.NotificationID)" :class="{ 'unread': !notification.Read, 'selected': isSelected(notification) }">
                <div class="user-img">
                    <img @click.stop="openProfile(notification.sender.UserTag)" :src="'/storage/' + notification.sender.ProfilePicture">
                </div>
                <div class="notification-content">
                    <div class="notification-text" v-if="notification.sender">
                        <p class="user-tag">{{ notification.sender.UserTag }}</p>
                        <p class="text">{{ notification.NotificationText }}</p>
                    </div>
                    <div class="time"><p>{{ notification.created_ago }}</p></div>
                </div>
                <div>
                    <input type="checkbox"            
                    :checked="isSelected(notification)"
                    @click.stop="markCheckbox(notification)"
                    :id="notification.NotificationID+'_notification'">
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState } from 'vuex';
import axios from 'axios';
export default{
    name: 'NotificationsContent',
    data() {
        return {
            notiType: 'all',
            notifications: {
                all: [],
                verified: [],
                mentions: [],
            },
            isMarkAllActive: false,
            isMarkReadActive: false,
            isMarkUnreadActive: false,
            selectedNotifications: [],
            all_count: 0,
            mentions_count: 0,
        };
    },
    computed: {
        ...mapState(['user']),
        currentNotifications() {
            if (this.notiType === 'all') {
                return this.notifications.all;
            }
            if (this.notiType === 'verified') {
                return this.notifications.verified;
            }
            if (this.notiType === 'mentions') {
                return this.notifications.mentions;
            }
            return [];
        }
    },
    methods: {
        async checkForNewNotifications() {
            try {
                const response = await axios.get(`/api/get-new-notifications/${this.notiType}`);
                const newNotifications = response.data.newNotifications;
                const newAll = [];
                const newVerified = [];
                const newMentions = [];

                newNotifications.forEach((notification) => {
                    if (notification.NotificationType === !'mention') {
                        newAll.push(notification);
                    } else if (notification.NotificationType === 'verified') {
                        newVerified.push(notification);                        
                    }
                    if (notification.NotificationType === 'mention') {
                        newMentions.push(notification);
                    }
                })
                this.notifications.all = [...newAll, ...this.notifications.all];
                this.notifications.verified = [...newVerified, ...this.notifications.verified];
                this.notifications.mentions = [...newMentions, ...this.notifications.mentions];

                // Update notification counts
                this.all_count += newAll.length;
                this.verified_count += newVerified.length;
                this.mentions_count += newMentions.length;
            } catch (error) {
                console.error(error);
            }
        },
        isSelected(notification) {
            return this.selectedNotifications.includes(notification);
        },
        markCheckbox(notification) {
            // Add or remove the notification from the selectedNotifications array
            if (this.selectedNotifications.includes(notification)) {
                this.selectedNotifications = this.selectedNotifications.filter(
                (selected) => selected !== notification
                );
            } else {
                this.selectedNotifications.push(notification);
            }
        },
        selectAll() {
            if (this.isMarkAllActive) {
                this.selectedNotifications = [];
            } else {
                this.selectedNotifications = [...this.currentNotifications];
            }

            // Toggle the active state
            this.isMarkAllActive = !this.isMarkAllActive;
        },
        selectRead() {
            if (this.isMarkReadActive) {
                this.selectedNotifications = this.selectedNotifications.filter(
                    (notification) => !notification.Read
                );
            } else {
                const readNotifications = this.currentNotifications.filter(
                    (notification) => notification.Read
                );
                this.selectedNotifications = [
                    ...this.selectedNotifications,
                    ...readNotifications,
                ];
            }
            this.isMarkReadActive = !this.isMarkReadActive;
        },
        selectUnread() {
            if (this.isMarkUnreadActive) {
                this.selectedNotifications = this.selectedNotifications.filter(
                    (notification) => notification.Read
                );
            } else {
                const unreadNotifications = this.currentNotifications.filter(
                    (notification) => !notification.Read
                );
                this.selectedNotifications = [
                    ...this.selectedNotifications,
                    ...unreadNotifications,
                ];
            }
            this.isMarkUnreadActive = !this.isMarkUnreadActive;
        },
        markSelectedAsRead() {
            // Call the API to mark selected notifications as read
            const notificationIds = this.selectedNotifications.map(
                (notification) => notification.NotificationID
            );

            axios
                .post('/api/mark-selected-as-read-notifications', { notificationIds })
                .then((response) => {
                console.log(response.data.message);
                // Clear selectedNotifications array
                this.selectedNotifications = [];
                // Refresh notifications
                this.getUserNotifications(this.notiType);
                this[this.notiType+'_count'] = response.data.notification_count;
                })
                .catch((error) => {
                console.error(error);
                });
        },
        deleteSelected() {
            // Call the API to delete selected notifications
            const notificationIds = this.selectedNotifications.map(
                (notification) => notification.NotificationID
            );

            axios
                .post('/api/delete-selected-notifications', { notificationIds })
                .then((response) => {
                    console.log(response.data.message);
                    // Clear selectedNotifications array
                    this.selectedNotifications = [];
                    // Refresh notifications
                    this.getUserNotifications(this.notiType);
                    this[this.notiType+'_count'] = response.data.notification_count;
                })
                .catch((error) => {
                    console.error(error);
                });
        },

        goBack() {
            this.$router.go(-1);
        },
        switchToAll() {
            this.notiType = 'all';
        },
        switchToVerified() {
            this.notiType = 'verified';
        },
        switchToMentions() {
            this.notiType = 'mentions';
        },
        openLink(link, id){
            axios
                .post('/api/mark-selected-as-read-notifications', { notificationIds: [id] })
                .then((response) => {
                    console.log(response.data.message);
                    this.$router.push(link);
                })
                .catch((error) => {
                    console.error(error);
                })
        },
        openProfile(tag){
            const NoSymbolTag = tag.replace(/^@/, '');
            this.$router.push('/profile/' + NoSymbolTag);
            console.log(tag);
        },
        async getUserNotifications(type){
        axios
            .get('/api/get-notifications/' + type)
            .then(response => {
                this.notifications[type] = response.data.notifications;
                this[type+'_count'] = response.data.notification_count;
            })
            .catch(error => {
                console.error(error);
            });
        },
    },
    async mounted() {
        await this.$store.dispatch('initializeApp');
        this.getUserNotifications('all');
        this.getUserNotifications('mentions');

        this.checkForNewNotificationsInterval = setInterval(this.checkForNewNotifications, 10000);
    },
    beforeUnmount() {
        clearInterval(this.checkForNewNotificationsInterval);
    },

    beforeRouteLeave(to, from, next) {
        clearInterval(this.checkForNewNotificationsInterval);
    }
}
</script>
<style lang="scss" scoped>

input[type="checkbox"]{
    appearance: none;
    width:20px;
    height:20px;
    background-color: #202223;
    border-radius:5px;
    border:#2F3336 solid 2px;
    display:flex;
    align-items:center;
    justify-content:center;
    cursor:pointer;
    transition:all 0.3s;
    &::after{
        color:#1d9bf0;
        content:"✓";
        display:none;
    }
    &:checked::after{
        display:block;
    }
    &:hover{
        background-color: rgb(29, 31, 33);
    }
}
.top-bar{
    position:sticky;
    top:0px;
    background-color:rgba($color: #000000, $alpha: 0.8);
    backdrop-filter: blur(5px);
    z-index:10;
    .top-top{
        border:none;
        background:none;
        backdrop-filter: none;
        padding-bottom:5px;
    }
    .noti-type{
        width:100%;
        height:50px;
        display:flex;
        flex-direction: row;
        align-items: center;
        justify-content: space-evenly;
        box-sizing: border-box;
        margin-top:10px;
        border-bottom:solid 1px #2F3336;
        .noti-type-btn{
            height:100%;
            width:100%;
            position:relative;
            align-items: center;
            justify-content: center;
            color:#71767B;
            padding:10px;
            border:none;
            background:none;
            transition:all 0.3s;
            font-size:16px;
            font-weight:600;
            &:hover{
                background-color:rgba($color: #202223, $alpha: 0.8);
            }
            .noti-title{
                display:flex;
                flex-direction: row;
                align-items: center;
                justify-content: center;
                gap:2px;
                .noti-count{
                    background-color: #1d9bf0;
                    font-size:14px;
                    border-radius:20px;
                    right:5px;
                    min-width:20px;
                    max-width:40px;
                    height:20px;
                    color:white;
                    display:flex;
                    align-items: center;
                    justify-content: center;
                }
            }
        }
        .active-noti-type{
            color:white;
            font-weight: 700;
        }
        .active-line{
            height:4px;
            width:75px;
            background-color: #1D9BF0;
            border-radius:5px;
            position:absolute;
            bottom:1px;
            display:none;
            left: 50%;
            transform: translateX(-50%);
            &.active{
                display:block;
            }
        }

    }
    .notification-buttons{
        width:100%;
        height:40px;
        display:flex;
        flex-direction: row;
        align-items: center;
        box-sizing: border-box;
        border-bottom:solid 1px #2F3336;
        .noti-btn{
            height:100%;
            width:auto;
            display:flex;
            align-items: center;
            justify-content: center;
            color:#71767B;
            border:none;
            background:none;
            transition:all 0.3s;
            font-size:18px;
            font-weight:600;
            cursor:pointer;
            &:hover{
                background-color:rgba($color: #202223, $alpha: 0.8);
            }
            ion-icon{
                color:#a9adb1;
            }
/*             .delete{
                color:#e42020;
            } */
        }
    }
}
.notifications-container{
    width: 100%;
    height: auto;
    box-sizing: border-box;
    .noti-main {
        padding-top: 0;
        padding-bottom: 80px;
        width: 100%;
        height: auto;
        display: flex;
        flex-direction: column;
        box-sizing: border-box;
        .main-text {
            color: white;
            text-align: left;
            font-size: 37px;
            font-weight: bold;
            margin-top: 10%;
            margin-left: 15%;
            margin-right: 15%;
        }
        .under-text {
            max-width: 100%;
            color: gray;
            font-size: 16px;
            text-align: left;
            margin-top: 10px;
            margin-left: 15%;
            margin-right: 15%;
        }
    }
}
.notification-container{
    color:white;
    box-sizing: border-box;
    height:auto;
    padding-bottom:125px;
    .notification{
        position:relative;
        width:100%;
        height:auto;
        display:flex;
        flex-direction:row;
        align-items: center;
        box-sizing: border-box;
        gap:10px;
        padding:5px 15px;
        border-bottom:solid 1px #2F3336;
        cursor:pointer;
        .user-img{
            min-width:50px;
            min-height:50px;
            max-width:50px;
            max-height:50px;
            border-radius: 50%;
            overflow: hidden;
            img{
                width:100%;
                height:100%;
                object-fit: cover;
            }
        }
        .notification-content{
            width:100%;
            font-size:16px;
            display:flex;
            align-items: center;
            justify-content: space-between;
            .notification-text{
                height:auto;
                width:100%;
                display:flex;
                flex-direction: column;
                gap:5px;
                position: relative;
                .text{
                    margin:0;
                    padding-top:20px;
                }
                .user-tag{
                    position:absolute;
                    top:0px;
                    margin:0;
                    font-weight: bold;
                    max-width:100%;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                }
            }
            .time{
                color:#6A6F74;
                margin-left:5px;
                font-size: 14px;
            }
        }
        &:hover{
            background-color: #080808;
        }
    }
    .unread{
        background-color: rgba($color: #1D9BF0, $alpha: 0.1);
        &:hover{
            background-color: rgba($color: #1D9BF0, $alpha: 0.12);
        }
    }
    .selected{
        background-color: rgba($color: #1D9BF0, $alpha: 0.15);
        &:hover{
            background-color: rgba($color: #1D9BF0, $alpha: 0.17);
        }
    }
}
@media (max-width: 500px){
.top-bar{
    .noti-type{
        height:35px;
        margin-top:5px;
        .noti-type-btn{
            padding:5px;
            font-size: 14px;
            font-weight:600;
            .noti-title{
                .noti-count{
                    font-size:12px;
                    width:15px;
                    height:15px;
                }
            }
        }
        .active-line{
            height:3px;
            width:50px;
        }
    }
    .notification-buttons{
        height:auto;
        min-height:30px;
        .noti-btn{
            font-size:15px;
        }
    }
}
.notifications-container{
    width: 100%;
    height: auto;
    box-sizing: border-box;
    .noti-main{
        .main-text{
            font-size: 25px;
        }
        .under-text{
            font-size: 14px;
        }
    }
}
.notification-container{
    color:white;
    box-sizing: border-box;
    .notification{
        padding:5px 10px;
        gap:5px;
        .user-img{
            min-width:40px;
            min-height:40px;
            max-width:40px;
            max-height:40px;
        }
        .notification-content{
            font-size:13px;
            .notification-text{
                gap:5px;
                .text{
                    margin:0;
                    padding-top:20px;
                }
            }
        }
    }
}
}
</style>