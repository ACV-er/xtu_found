<style scoped>
    .operate {
        display: inline-block;
        margin: 0 5px;
        font-size: 12px;
        color: rgb(0,193,222);
        cursor:pointer;
    }

    .operate:hover {
        color: rgb(0, 177, 205);
    }

    .operate:active {
        color: rgb(0, 104, 128);
    }

    th {
        color: rgb(102,102,102);
        font-size: 12px;
        padding: 5px 10px 5px 4px;
    }

    tr:nth-child(odd), tr:nth-child(odd)>th{
        background-color: rgb(255,255,255);
    }

    tr:nth-child(even), tr:nth-child(even)>th{
        background-color: rgb(242,242,242);
    }

    table {
        display: inline-block;
        border-collapse:collapse;
        margin-top: 40px;

    }

    .main {
        display: flex;
        justify-content:center;
        min-width: 1000px;
    }

    .title {
        max-width: 100px;
    }

    .description {
        max-width: 200px;
    }

    .title, .description {
        white-space:nowrap;
        text-overflow:ellipsis;
        overflow:hidden;
    }

    .allInfo {
        position: fixed;
        top: 0; right: 0; bottom: 0; left: 0;
        display: flex;
        justify-content:center;
        background-color: rgba(0,0,0,.3);
    }

    .m-allInfo {
        position: absolute;
        margin: auto;
        top: 0; right: 0; bottom: 0; left: 0;

        display: flex;
        flex-wrap: wrap;
        align-items: center;

        width: 500px;
        height: 300px;
        background-color: white;
        border-radius: 5px;
    }

    .from_button {
        width: 50px;
        height: 50px;
        border: 0;
        background-color: #06C;
        color: white;
        border-radius: 50px;
        margin:0 auto;
    }

    .title_input {
        height: 3em;
    }

    .description_input {
        height: 5em;
    }

    .title_input, .description_input {
        width: 60%;
        margin:0 auto;
    }

    .button_box {
        width: 100%;
        display: flex;
        justify-content:center;
    }

    #search {
        position: relative;
        margin-top: 20px;
    }

    .search {
        display: flex;
        flex-direction: column;
        justify-content:center;
        min-width: 1000px;
    }

    #keyword {
        width: 220px;
        height: 30px;
        position: absolute;
        top: 0; left: 0; bottom: 0; right: 0;
        margin: auto;
    }
</style>

<template>
    <div>
        <div class="search">
            <form action="" id="search" v-on:submit.prevent="search()">
                <input id="keyword" type="search" v-model="keyword" placeholder="请输入查找关键字 id, nickname">
            </form>
            <table v-if="userShow" style="display: table">
                <thead>
                <tr>
                    <th>id</th>
                    <th>昵称</th>
                    <th>班级</th>
                    <th>学号</th>
                    <th>QQ</th>
                    <th>Phone</th>
                    <th>微信</th>
                    <th>被拉黑次数</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="user in users">
                    <th>{{ user.id }}</th>
                    <th>{{ user.nickname }}</th>
                    <th>{{ user.class }}</th>
                    <th>{{ user.stu_id }}</th>
                    <th>{{ user.qq }}</th>
                    <th>{{ user.phone }}</th>
                    <th>{{ user.wx }}</th>
                    <th>{{ user.black }}</th>
                    <th>
                        <div v-if="user.black > 1" class="operate" v-on:click="unblack(user.id)">取消拉黑</div>
                        <div v-else class="operate" v-on:click="black(user.id)">拉黑</div> |
                        <div class="operate" v-on:click="getUserPost(user.id)">查看该用户所有帖子</div>
                    </th>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="main">
            <table v-if="postShow">
                <thead>
                <tr>
                    <th>id</th>
                    <th>类型</th>
                    <th class="title">标题</th>
                    <th class="description">描述</th>
                    <th>校园卡</th>
                    <th>地点</th>
                    <th>日期</th>
                    <th>标记</th>
                    <th>解决</th>
                    <th>最后更新时间</th>
                    <th>操作</th>
                </tr>
                </thead>
                <tbody>
                <tr v-for="post in posts">
                    <th>{{ post.id }}</th>
                    <th v-if="post.type === 1">失物</th>
                    <th v-else>招领</th>
                    <th class="title">{{ post.title }}</th>
                    <th class="description">{{ post.description }}</th>
                    <th v-if="post.stu_card === 1">是</th>
                    <th v-else>否</th>
                    <th>{{ post.address }}</th>
                    <th>{{ post.date }}</th>
                    <th v-if="post.mark === 1">是</th>
                    <th v-else>否</th>
                    <th v-if="post.solve === 1">是</th>
                    <th v-else>否</th>
                    <th>{{ post.updated_at }}</th>
                    <th>
                        <div class="operate" v-on:click="deletePost(post.id)">删除</div> |
                        <div class="operate" v-on:click="getAllInfo(post.id)">修改</div> |
                        <div class="operate" v-on:click="getUploader(post.user_id)">转到发布者</div>
                    </th>
                </tr>
                </tbody>
            </table>
        </div>

        <div class="allInfo" v-if="allInfoShow">
            <div class="m-allInfo">
                <textarea class="title_input" v-model="allInfo.title">{{ allInfo.title }}</textarea>
                <textarea class="description_input" v-model="allInfo.description">{{ allInfo.description }}</textarea>
                <div class="button_box">
                    <button class ="from_button" v-on:click="allInfo={};allInfoShow=false;">取消</button>
                    <button class ="from_button" v-on:click="updateInfo()">确定</button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
    export default {
        data() {
            return {
                nowUserId: -1,
                userShow: false,
                users: [],
                keyword: '',
                postShow: false,
                posts: [],
                allInfoShow: false,
                allInfo:{}
            }
        },
        methods: {
            getUserPost(id) {
                this.nowUserId = id;
                this.postShow = true;
                let obj = this;
                let ajax = new XMLHttpRequest();
                ajax.onreadystatechange = function () {
                    if (ajax.readyState === 4 && ajax.status === 200) {
                        obj.posts = JSON.parse(ajax.responseText).data;
                        if( obj.posts.length ===  0) {
                            alert("该用户还没有发过帖子呢");
                        }
                    }
                };
                ajax.open("GET", "https://found.sky31.com/user/laf/"+id, true);//false同步    true异步
                ajax.send();
                return true;
            },
            getAllInfo(id) {
                window.axios.get('https://found.sky31.com/laf/' + id).then(({ data }) => {
                    this.allInfo = data.data;
                    this.allInfoShow = true;
                });
            },
            updateInfo() {
                let data = 'title='+this.allInfo.title+'&description='+this.allInfo.description;
                window.axios.post(`https://found.sky31.com/manager/post/update/` + this.allInfo.id, data).then(({ data }) => {
                    if(data.code === 0) {
                        this.allInfo={};
                        this.allInfoShow=false;
                        this.getUserPost(this.nowUserId);
                    } else {
                        alert('失败' + data.status + '\n' + data.data);
                    }
                });
            },
            deletePost(id) {
                let r=confirm("你确认删除么?");
                if (r === true){
                    window.axios.get('https://found.sky31.com/manager/post/delete/' + id).then(({ data }) => {
                        if(data.code === 0) {
                            this.getUserPost(this.nowUserId);
                            alert('成功');
                        } else {
                            alert(data.status + '\n' + data.data);
                        }
                    });
                }
            },
            getUploader(id) {
                alert('开发中,发布者id：' + id + ',请去用户搜索该用户!')
            },
            search(){
                let keyword = this.keyword.trim().split(/\s/);
                window.axios.post(`https://found.sky31.com/user/search` , "keyword="+JSON.stringify(keyword)).then(({ data }) => {
                    if(data.code === 0) {
                        if( data.data.length ===  0) {
                            alert("查无此人");
                        }
                        this.users=data.data;
                        this.userShow=true;
                    } else {
                        alert('失败' + data.status + '\n' + data.data);
                    }

                });
            },
            black(id) {
                let r=confirm("你确认拉黑么?");
                if (r === true){
                    window.axios.get('https://found.sky31.com/user/black/' + id).then(({ data }) => {
                        if(data.code === 0) {
                            this.search();
                            alert('成功');
                        } else {
                            alert(data.status + '\n' + data.data);
                        }
                    });
                }
            },
            unblack(id) {
                let r=confirm("你确认取消拉黑么?");
                if (r === true){
                    window.axios.get('https://found.sky31.com/user/unblack/' + id).then(({ data }) => {
                        if(data.code === 0) {
                            this.search();
                            alert('成功');
                        } else {
                            alert(data.status + '\n' + data.data);
                        }
                    });
                }
            }
        }
    }
</script>
