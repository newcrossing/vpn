<?php

namespace App\Http\Controllers\Api;


use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserAuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $registerUserData = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|min:8'
        ], $messages = [
            'required' => 'Поле :attribute является обязательным.',
            'email' => ':attribute должен быть действительным адресом.',
            'unique' => 'Пользователь с таким :attribute уже зарегистрирован.',
            'min' => 'Минимальная длина поля :attribute 8 символов.',
        ], $attributes = [
            'name' => 'Имя',
            'email' => 'E-mail',
            'password' => 'Пароль',
        ]);

        try {
            $user = User::create([
                'name' => $registerUserData['name'],
                'email' => $registerUserData['email'],
                'password' => Hash::make($registerUserData['password']),
            ]);

            $tokenName = 'fundaToken' . rand(111, 999);
            $token = $user->createToken($tokenName)->plainTextToken;

            return response()->json([
                'status' => 201,
                'message' => 'Пользователь создан',
                'data' => [
                    'user' => $user,
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ]
            ], 201);

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Что-то пошло не так ' . $th->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'min:8'],
        ], $messages = [
            'required' => 'Поле :attribute является обязательным.',
            'email' => ':attribute должен быть действительным адресом.',
            'min' => 'Минимальная длина поля :attribute 8 символов.',
        ], $attributes = [
            'email' => 'E-mail',
            'password' => 'Пароль',
        ]);

        try {
            $user = User::where('email', $credentials['email'])->first();

            if (!$user || !Hash::check($credentials['password'], $user->password)) {
                return response()->json(['message' => 'Ошибка авторизации'], 401);
            }

            if (Auth::attempt($credentials)) {

                $tokenName = 'fundaToken' . rand(111, 999);
                $token = $user->createToken($tokenName, ['server:update'], now()->addWeek())->plainTextToken;

                return response()->json([
                    'status' => 200,
                    'message' => 'Авторизация успешна',
                    'data' => [
                        'user' => $user
                    ],
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ], 200);
            } else {
                return response()->json(['message' => 'Некорректные полномочия'], 401);
            }

        } catch (\Throwable $th) {
            return response()->json([
                'message' => 'Что-то пошло не так ' . $th->getMessage(),
                'status' => 500
            ], 500);
        }
    }

    public function logout()
    {
        $user = User::findOrFail(Auth::id());
        $user->tokens()->delete();

        return response()->json([
            'status' => 200,
            'message' => 'Пользователь вышел'
        ], 200);
    }

    public function user()
    {
        if (Auth::check()) {

            $user = Auth::user();

            return response()->json([
                'message' => 'Пользователь детально',
                'data' => $user,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Войдите в систему, чтобы продолжить'
            ], 200);
        }
    }

    public function user_edit(Request $request)
    {

        if (Auth::check()) {

            $userData = $request->validate([
                'name' => 'required|string',
            ], $messages = [
                'required' => 'Поле :attribute является обязательным.',
            ], $attributes = [
                'name' => 'Имя',
            ]);


            $user = User::find(Auth::id());
            $user->name = $userData['name']?:'';
            $user->save();

            return response()->json([
                'message' => 'Изменения сохранены',
                'data' => $user,
            ], 200);
        } else {
            return response()->json([
                'message' => 'Войдите в систему, чтобы продолжить'
            ], 200);
        }
    }
}
