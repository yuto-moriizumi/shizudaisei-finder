export type User = {
    ID: string;
    USER_NAME: string;
    USER_SCREEN_NAME: string;
    IMG: string;
    CONTENT: string;
    CREATED_AT: string;
    IS_FOLLOWING?: boolean;
};

export type UserResponce = {
    id:string;
    name: string;
    screen_name: string;
    img_url: string;
    content: string;
    created_at: string;
    is_following: boolean;
  };